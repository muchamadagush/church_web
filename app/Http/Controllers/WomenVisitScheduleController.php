<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\WomenVisitSchedule;
use Illuminate\Http\Request;

class WomenVisitScheduleController extends Controller
{
    public function index()
    {
        $schedules = WomenVisitSchedule::with('church')->orderBy('visit_date')->paginate(10);
        return view('worship-schedules.women-visits.index', compact('schedules'));
    }

    public function create()
    {
        $churches = Church::all();
        return view('worship-schedules.women-visits.form', compact('churches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'visit_date' => 'required|date',
            'worship_leader' => 'required|string|max:255',
            'preacher' => 'required|string|max:255'
        ]);

        WomenVisitSchedule::create($validated);
        return redirect()->route('worship-schedules.women-visits.index')
                        ->with('success', 'Jadwal kunjungan berhasil ditambahkan');
    }

    public function edit(WomenVisitSchedule $schedule)
    {
        $churches = Church::all();
        return view('worship-schedules.women-visits.form', compact('schedule', 'churches'));
    }

    public function update(Request $request, WomenVisitSchedule $schedule)
    {
        $validated = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'visit_date' => 'required|date',
            'worship_leader' => 'required|string|max:255',
            'preacher' => 'required|string|max:255'
        ]);

        $schedule->update($validated);
        return redirect()->route('worship-schedules.women-visits.index')
                        ->with('success', 'Jadwal kunjungan berhasil diperbarui');
    }

    public function destroy(WomenVisitSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('worship-schedules.women-visits.index')
                        ->with('success', 'Jadwal kunjungan berhasil dihapus');
    }
}