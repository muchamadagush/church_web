<?php

namespace App\Http\Controllers;

use App\Models\YouthVisitSchedule;
use App\Models\Church;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\PermissionHelper;

class YouthVisitScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = YouthVisitSchedule::with('church')
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $today = Carbon::today();
        $hasTodaySchedules = YouthVisitSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();
        return view('worship-schedules.youth-visit.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.youth-visit.create', compact('churches'));
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
            'worship_leader' => 'required|string|max:255|different:speaker',
            'speaker' => 'required|string|max:255|different:worship_leader',
        ]);

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        // Prevent double-booking the same person in any role within overlapping time
        $personConflict = YouthVisitSchedule::where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->where(function ($q) use ($request) {
                $q->where('worship_leader', $request->worship_leader)
                  ->orWhere('speaker', $request->worship_leader)
                  ->orWhere('worship_leader', $request->speaker)
                  ->orWhere('speaker', $request->speaker);
            })
            ->exists();

        if ($personConflict) {
            return back()->withInput()->withErrors([
                'worship_leader' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
                'speaker' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
            ]);
        }

        YouthVisitSchedule::create([
            'start_datetime' => $start,
            'end_datetime' => $end,
            'church_id' => $request->church_id,
            'worship_leader' => $request->worship_leader,
            'speaker' => $request->speaker,
        ]);

        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', 'Tambah Jadwal Kunjungan Kaum Muda Berhasil');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\YouthVisitSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(YouthVisitSchedule $schedule)
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.youth-visit.edit', compact('schedule', 'churches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\YouthVisitSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, YouthVisitSchedule $schedule)
    {
        $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'church_id' => 'required|exists:churches,id',
            'worship_leader' => 'required|string|max:255|different:speaker',
            'speaker' => 'required|string|max:255|different:worship_leader',
        ]);

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        // Prevent double-booking for overlapping time on update (exclude self)
        $personConflict = YouthVisitSchedule::where('id', '!=', $schedule->id)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->where(function ($q) use ($request) {
                $q->where('worship_leader', $request->worship_leader)
                  ->orWhere('speaker', $request->worship_leader)
                  ->orWhere('worship_leader', $request->speaker)
                  ->orWhere('speaker', $request->speaker);
            })
            ->exists();

        if ($personConflict) {
            return back()->withInput()->withErrors([
                'worship_leader' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
                'speaker' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
            ]);
        }

        $schedule->update([
            'start_datetime' => $start,
            'end_datetime' => $end,
            'church_id' => $request->church_id,
            'worship_leader' => $request->worship_leader,
            'speaker' => $request->speaker,
        ]);

        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', 'Update Jadwal Kunjungan Kaum Muda Berhasil');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\YouthVisitSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(YouthVisitSchedule $schedule)
    {
        $schedule->delete();
        
        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', 'Hapus Jadwal Kunjungan Kaum Muda Berhasil');
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
        $existingCount = YouthVisitSchedule::whereBetween('start_datetime', [$date, $dateEnd])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.youth-visit.index')
                ->with('error', 'Tanggal tersebut sudah memiliki jadwal. Generate hanya untuk hari yang masih kosong.');
        }

        // Get all churches
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.youth-visit.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // Greedy algorithm: Schedule churches sequentially with breaks
    $currentTime = Carbon::parse($date->format('Y-m-d') . ' 09:00');
        $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.youth-visit.index')
                ->with('error', 'Durasi tidak valid.');
        }
        $breakMinutes = 15;
        $created = 0;

        foreach ($churches as $church) {
            $startDatetime = $currentTime->copy();
            $endDatetime = $startDatetime->copy()->addMinutes((int) $duration);

            // Check overlap before creating
            $autoLeader = 'WL ' . $church->name;
            $autoSpeaker = 'Pembicara ' . $church->name;
            $overlap = YouthVisitSchedule::where(function($q) use ($autoLeader, $autoSpeaker){
                    $q->where('worship_leader', $autoLeader)
                      ->orWhere('speaker', $autoSpeaker);
                })
                ->where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();

            if (!$overlap) {
                YouthVisitSchedule::create([
                    'church_id' => $church->id,
                    'worship_leader' => $autoLeader,
                    'speaker' => $autoSpeaker,
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                ]);
                $created++;
            }

            // Move to next time slot
            $currentTime->addMinutes((int) $duration + $breakMinutes);
        }

        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', "Berhasil generate {$created} jadwal kunjungan kaum muda.");
    }
}
