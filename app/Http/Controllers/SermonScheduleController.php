<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Church;
use App\Models\SermonSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SermonScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = SermonSchedule::with('church')
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $today = Carbon::today();
        $hasTodaySchedules = SermonSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();

        return view('worship-schedules.sermons.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.sermons.create', compact('churches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'church_id' => 'required|exists:churches,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ], [
            'pengkhotbah.required' => 'Pengkhotbah wajib diisi.',
            'church_id.required' => 'Gereja wajib dipilih.',
            'start_datetime.required' => 'Waktu mulai wajib diisi.',
            'end_datetime.required' => 'Waktu selesai wajib diisi.',
            'end_datetime.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            $start = Carbon::parse($validated['start_datetime']);
            $end = Carbon::parse($validated['end_datetime']);
        } catch (\Exception $e) {
            return back()->withErrors(['start_datetime' => 'Format datetime tidak valid'])->withInput();
        }

        if ($end->lessThanOrEqualTo($start)) {
            return back()->withErrors(['end_datetime' => 'Waktu selesai harus lebih besar daripada waktu mulai'])->withInput();
        }

        // Global overlap check against existing flattened sermon schedules
        // Check if the same pengkhotbah is already scheduled at overlapping time
        $overlap = SermonSchedule::query()
            ->where('pengkhotbah', $validated['pengkhotbah'])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        if ($overlap) {
            return back()->withErrors(['pengkhotbah' => 'Pengkhotbah ini sudah dijadwalkan pada waktu yang bertumpuk.'])->withInput();
        }

        SermonSchedule::create([
            'pengkhotbah' => $validated['pengkhotbah'],
            'church_id' => $validated['church_id'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
        ]);

        return redirect()->route('worship-schedules.sermons.index')->with('success', 'Jadwal khotbah berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SermonSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(SermonSchedule $schedule)
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.sermons.edit', compact('schedule', 'churches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SermonSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SermonSchedule $schedule)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'church_id' => 'required|exists:churches,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ], [
            'pengkhotbah.required' => 'Pengkhotbah wajib diisi.',
            'church_id.required' => 'Gereja wajib dipilih.',
            'start_datetime.required' => 'Waktu mulai wajib diisi.',
            'end_datetime.required' => 'Waktu selesai wajib diisi.',
            'end_datetime.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            $start = Carbon::parse($validated['start_datetime']);
            $end = Carbon::parse($validated['end_datetime']);
        } catch (\Exception $e) {
            return back()->withErrors(['start_datetime' => 'Format datetime tidak valid'])->withInput();
        }

        if ($end->lessThanOrEqualTo($start)) {
            return back()->withErrors(['end_datetime' => 'Waktu selesai harus lebih besar daripada waktu mulai'])->withInput();
        }

        $overlap = SermonSchedule::query()
            ->where('id', '!=', $schedule->id)
            ->where('pengkhotbah', $validated['pengkhotbah'])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        if ($overlap) {
            return back()->withErrors(['pengkhotbah' => 'Pengkhotbah ini sudah dijadwalkan pada waktu yang bertumpuk.'])->withInput();
        }

        $schedule->update([
            'pengkhotbah' => $validated['pengkhotbah'],
            'church_id' => $validated['church_id'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
        ]);

        return redirect()->route('worship-schedules.sermons.index')->with('success', 'Jadwal khotbah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SermonSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(SermonSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('worship-schedules.sermons.index')
                        ->with('success', 'Jadwal khotbah berhasil dihapus');
    }

    /**
     * Generate schedule automatically using greedy algorithm
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'duration' => 'required|integer|min:30|max:480', // 30 minutes to 8 hours
        ]);

        $date = Carbon::today();
        $startTime = '09:00';
        // Cast duration explicitly to int to satisfy Carbon's strict type for addMinutes
        $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return back()->with('error', 'Durasi tidak valid.');
        }

        // Check if there are already schedules on this date
        $existingCount = SermonSchedule::whereDate('start_datetime', $date->format('Y-m-d'))->count();
        
        if ($existingCount > 0) {
            return back()->with('error', 'Sudah ada jadwal pada tanggal tersebut. Generate hanya untuk hari kosong.');
        }

        // Get all churches and pengkhotbah (assuming we have a list)
        $churches = Church::orderBy('name')->get();
        
        if ($churches->isEmpty()) {
            return back()->with('error', 'Tidak ada data gereja. Tambahkan gereja terlebih dahulu.');
        }

        // Greedy algorithm: try to schedule as many churches as possible
        $scheduled = [];
    $currentStart = Carbon::parse($date->format('Y-m-d') . ' ' . $startTime);

        foreach ($churches as $index => $church) {
            $currentEnd = $currentStart->copy()->addMinutes((int) $duration);
            
            // For demo, use church name as pengkhotbah or get from pool
            $pengkhotbah = 'Gembala ' . $church->name;

            // Check overlap with already scheduled items
            $hasOverlap = false;
            foreach ($scheduled as $s) {
                if ($currentStart < $s['end'] && $currentEnd > $s['start']) {
                    $hasOverlap = true;
                    break;
                }
            }

            if (!$hasOverlap) {
                SermonSchedule::create([
                    'pengkhotbah' => $pengkhotbah,
                    'church_id' => $church->id,
                    'start_datetime' => $currentStart,
                    'end_datetime' => $currentEnd,
                ]);

                $scheduled[] = [
                    'start' => $currentStart->copy(),
                    'end' => $currentEnd->copy(),
                ];

                // Move to next slot
                $currentStart = $currentEnd->copy()->addMinutes(15); // 15 min break
            }
        }

        $count = count($scheduled);
        return redirect()->route('worship-schedules.sermons.index')
                        ->with('success', "Berhasil generate {$count} jadwal khotbah secara otomatis.");
    }
}