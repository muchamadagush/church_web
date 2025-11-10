<?php

namespace App\Http\Controllers;

use App\Models\ChristmasSchedule;
use App\Models\Church;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\PermissionHelper;

class ChristmasScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = ChristmasSchedule::with('church')
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $today = Carbon::today();
        $hasTodaySchedules = ChristmasSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();
        return view('worship-schedules.christmas.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.christmas.create', compact('churches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'church_id' => 'required|exists:churches,id',
        ]);

        // Overlap check: any schedule whose time range intersects the new range
        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        $overlap = ChristmasSchedule::where(function ($q) use ($start, $end) {
            $q->where(function ($inner) use ($start, $end) {
                $inner->where('start_datetime', '<', $end)
                      ->where('end_datetime', '>', $start);
            });
        })->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['start_datetime' => 'Rentang waktu bentrok dengan jadwal Natal lain.']);
        }

        ChristmasSchedule::create([
            'start_datetime' => $start,
            'end_datetime' => $end,
            'church_id' => $request->church_id,
        ]);

        return redirect()->route('worship-schedules.christmas.index')
            ->with('success', 'Tambah Jadwal Natal Berhasil');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChristmasSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(ChristmasSchedule $schedule)
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.christmas.edit', compact('schedule', 'churches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChristmasSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ChristmasSchedule $schedule)
    {
        $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'church_id' => 'required|exists:churches,id',
        ]);

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        $overlap = ChristmasSchedule::where('id', '!=', $schedule->id)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_datetime', '<', $end)
                  ->where('end_datetime', '>', $start);
            })->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['start_datetime' => 'Rentang waktu bentrok dengan jadwal Natal lain.']);
        }

        $schedule->update([
            'start_datetime' => $start,
            'end_datetime' => $end,
            'church_id' => $request->church_id,
        ]);

        return redirect()->route('worship-schedules.christmas.index')
            ->with('success', 'Update Jadwal Natal Berhasil');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChristmasSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChristmasSchedule $schedule)
    {
        $schedule->delete();
        
        return redirect()->route('worship-schedules.christmas.index')
            ->with('success', 'Hapus Jadwal Natal Berhasil');
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
        $existingCount = ChristmasSchedule::whereBetween('start_datetime', [$date, $dateEnd])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.christmas.index')
                ->with('error', 'Tanggal tersebut sudah memiliki jadwal. Generate hanya untuk hari yang masih kosong.');
        }

        // Get all churches
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.christmas.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // Greedy algorithm: Schedule churches sequentially with breaks
    $currentTime = Carbon::parse($date->format('Y-m-d') . ' 09:00');
        $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.christmas.index')
                ->with('error', 'Durasi tidak valid.');
        }
        $breakMinutes = 15;
        $created = 0;

        foreach ($churches as $church) {
            $startDatetime = $currentTime->copy();
            $endDatetime = $startDatetime->copy()->addMinutes((int) $duration);

            // Check overlap before creating
            $overlap = ChristmasSchedule::where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();

            if (!$overlap) {
                ChristmasSchedule::create([
                    'church_id' => $church->id,
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                ]);
                $created++;
            }

            // Move to next time slot
            $currentTime->addMinutes((int) $duration + $breakMinutes);
        }

        return redirect()->route('worship-schedules.christmas.index')
            ->with('success', "Berhasil generate {$created} jadwal Natal.");
    }
}
