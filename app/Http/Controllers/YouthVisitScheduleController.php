<?php

namespace App\Http\Controllers;

use App\Models\YouthVisitSchedule;
use App\Models\Church;
use Illuminate\Http\Request;
use Carbon\Carbon;

class YouthVisitScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = YouthVisitSchedule::with('church')->orderBy('schedule_date', 'asc')->get();
        return view('worship-schedules.youth-visit.index', compact('schedules'));
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
            'schedule_date' => 'required|date',
            'church_id' => 'required|exists:churches,id',
            'time' => 'required',
            'worship_leader' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
        ]);

        YouthVisitSchedule::create([
            'schedule_date' => $request->schedule_date,
            'church_id' => $request->church_id,
            'time' => $request->time,
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
            'schedule_date' => 'required|date',
            'church_id' => 'required|exists:churches,id',
            'time' => 'required',
            'worship_leader' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
        ]);

        $schedule->update([
            'schedule_date' => $request->schedule_date,
            'church_id' => $request->church_id,
            'time' => $request->time,
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
}