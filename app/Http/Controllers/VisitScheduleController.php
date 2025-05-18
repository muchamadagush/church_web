<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\VisitSchedule;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;

class VisitScheduleController extends Controller
{
    public function index()
    {
        $schedules = VisitSchedule::with('church')->orderBy('visit_date')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        
        return view('worship-schedules.visits.index', compact('schedules', 'canEdit', 'canDelete'));
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
            'visit_date' => 'required|date'
        ]);

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
            'visit_date' => 'required|date'
        ]);

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
}