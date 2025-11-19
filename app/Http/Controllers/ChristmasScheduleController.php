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
            'end_datetime' => 'required|date',
            'church_id' => 'required|exists:churches,id',
        ]);

        // Overlap check: any schedule whose time range intersects the new range
        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        // Validate end_datetime is after start_datetime
        if ($end->lte($start)) {
            return back()->withInput()->withErrors([
                'end_datetime' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
        }

        // Only allow schedules in December and within the same year
        if ($start->month !== 12 || $end->month !== 12 || $start->year !== $end->year) {
            return back()->withInput()->withErrors([
                'start_datetime' => 'Jadwal Natal hanya boleh pada bulan Desember.'
            ]);
        }

        // Each church can only have one Christmas schedule per year
        $existsSameYearSameChurch = ChristmasSchedule::where('church_id', $request->church_id)
            ->whereYear('start_datetime', $start->year)
            ->exists();
        if ($existsSameYearSameChurch) {
            return back()->withInput()->withErrors([
                'church_id' => 'Gereja ini sudah memiliki Jadwal Natal pada tahun tersebut.'
            ]);
        }

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
            'end_datetime' => 'required|date',
            'church_id' => 'required|exists:churches,id',
        ]);

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        // Validate end_datetime is after start_datetime
        if ($end->lte($start)) {
            return back()->withInput()->withErrors([
                'end_datetime' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
        }

        // Only allow schedules in December and within the same year
        if ($start->month !== 12 || $end->month !== 12 || $start->year !== $end->year) {
            return back()->withInput()->withErrors([
                'start_datetime' => 'Jadwal Natal hanya boleh pada bulan Desember.'
            ]);
        }

        // Each church can only have one Christmas schedule per year (exclude current)
        $existsSameYearSameChurch = ChristmasSchedule::where('id', '!=', $schedule->id)
            ->where('church_id', $request->church_id)
            ->whereYear('start_datetime', $start->year)
            ->exists();
        if ($existsSameYearSameChurch) {
            return back()->withInput()->withErrors([
                'church_id' => 'Gereja ini sudah memiliki Jadwal Natal pada tahun tersebut.'
            ]);
        }

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
}
