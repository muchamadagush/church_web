<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\VisitSchedule;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Carbon\Carbon;

class VisitScheduleController extends Controller
{
    public function index()
    {
        $schedules = VisitSchedule::with('church')->orderBy('start_datetime')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        
    $today = Carbon::today();
    $hasTodaySchedules = VisitSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();
    return view('worship-schedules.visits.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules'));
    }

    public function create()
    {
        $churches = Church::all();
        return view('worship-schedules.visits.form', compact('churches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime'
        ]);

        $start = Carbon::parse($validated['start_datetime']);
        $end = Carbon::parse($validated['end_datetime']);

        // Cek overlap untuk semua jadwal (tidak peduli gerejanya)
        $overlapExists = VisitSchedule::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_datetime', [$start, $end])
              ->orWhereBetween('end_datetime', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  $q2->where('start_datetime', '<=', $start)
                     ->where('end_datetime', '>=', $end);
              });
        })->exists();

        if ($overlapExists) {
            return back()->withErrors(['schedule_conflict' => 'Jadwal bentrok dengan jadwal kunjungan lain. Ketua wilayah tidak dapat berada di dua tempat pada waktu yang sama.'])->withInput();
        }

        VisitSchedule::create($validated);
        return redirect()->route('worship-schedules.visits.index')
                        ->with('success', 'Jadwal kunjungan berhasil ditambahkan');
    }

    public function edit(VisitSchedule $schedule)
    {
        $churches = Church::all();
        return view('worship-schedules.visits.form', compact('schedule', 'churches'));
    }

    public function update(Request $request, VisitSchedule $schedule)
    {
        $validated = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime'
        ]);

        $start = Carbon::parse($validated['start_datetime']);
        $end = Carbon::parse($validated['end_datetime']);

        // Cek overlap untuk semua jadwal kecuali jadwal yang sedang diupdate
        $overlapExists = VisitSchedule::where('id', '!=', $schedule->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                  ->orWhereBetween('end_datetime', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_datetime', '<=', $start)
                         ->where('end_datetime', '>=', $end);
                  });
            })->exists();

        if ($overlapExists) {
            return back()->withErrors(['schedule_conflict' => 'Jadwal bentrok dengan jadwal kunjungan lain. Ketua wilayah tidak dapat berada di dua tempat pada waktu yang sama.'])->withInput();
        }

        $schedule->update($validated);
        return redirect()->route('worship-schedules.visits.index')
                        ->with('success', 'Jadwal kunjungan berhasil diperbarui');
    }

    public function destroy(VisitSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('worship-schedules.visits.index')
                        ->with('success', 'Jadwal kunjungan berhasil dihapus');
    }

    /**
     * Generate schedules using greedy algorithm.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'duration' => 'required|integer|min:30|max:480',
        ]);

        $date = Carbon::today()->startOfDay();
        $dateEnd = $date->copy()->endOfDay();

        // Check if the date already has schedules
        $existingCount = VisitSchedule::whereBetween('start_datetime', [$date, $dateEnd])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Tanggal tersebut sudah memiliki jadwal. Generate hanya untuk hari yang masih kosong.');
        }

        // Get all churches
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // Greedy algorithm: Schedule churches sequentially with breaks
    $currentTime = Carbon::parse($date->format('Y-m-d') . ' 09:00');
    $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Durasi tidak valid.');
        }
        $breakMinutes = 15;
        $created = 0;

        foreach ($churches as $church) {
            $startDatetime = $currentTime->copy();
            $endDatetime = $startDatetime->copy()->addMinutes((int) $duration);

            // Check overlap before creating
            $overlap = VisitSchedule::where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();

            if (!$overlap) {
                VisitSchedule::create([
                    'church_id' => $church->id,
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                ]);
                $created++;
            }

            // Move to next time slot
            $currentTime->addMinutes((int) $duration + $breakMinutes);
        }


        return redirect()->route('worship-schedules.visits.index')
            ->with('success', "Berhasil generate {$created} jadwal kunjungan.");
    }
}
