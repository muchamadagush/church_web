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
        $schedules = ChristmasSchedule::with('church')->orderBy('schedule_date', 'asc')->get();

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');

        return view('worship-schedules.christmas.index', compact('schedules', 'canEdit', 'canDelete'));
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
            'schedule_date' => 'required|date',
            'church_id' => 'required|exists:churches,id',
        ]);

        ChristmasSchedule::create([
            'schedule_date' => $request->schedule_date,
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
            'schedule_date' => 'required|date',
            'church_id' => 'required|exists:churches,id',
        ]);

        $schedule->update([
            'schedule_date' => $request->schedule_date,
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
}