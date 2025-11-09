<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\WomenVisitSchedule;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;

class WomenVisitScheduleController extends Controller
{
    public function index()
    {
        $schedules = WomenVisitSchedule::with('church')->orderBy('start_datetime')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');

        return view('worship-schedules.women-visits.index', compact('schedules', 'canEdit', 'canDelete' ));
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
            'start_datetime' => 'required|date_format:Y-m-d\TH:i',
            'end_datetime' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
            'worship_leader' => 'required|string|max:255',
            'preacher' => 'required|string|max:255'
        ]);

        // Validasi bentrok jadwal
        $conflict = WomenVisitSchedule::where(function($q) use ($validated) {
                $q->where('worship_leader', $validated['worship_leader'])
                  ->where('preacher', $validated['preacher']);
            })
            ->where(function($q) use ($validated) {
                $q->where('start_datetime', '<', $validated['end_datetime'])
                  ->where('end_datetime', '>', $validated['start_datetime']);
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors([
                'schedule_conflict' => 'Jadwal bentrok dengan jadwal lain untuk pimpinan pujian dan pengkhotbah yang sama.'
            ]);
        }

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
            'start_datetime' => 'required|date_format:Y-m-d\TH:i',
            'end_datetime' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_datetime',
            'worship_leader' => 'required|string|max:255',
            'preacher' => 'required|string|max:255'
        ]);

        // Validasi bentrok jadwal (kecuali jadwal yang sedang diedit)
        $conflict = WomenVisitSchedule::where(function($q) use ($validated) {
                $q->where('worship_leader', $validated['worship_leader'])
                  ->where('preacher', $validated['preacher']);
            })
            ->where(function($q) use ($validated) {
                $q->where('start_datetime', '<', $validated['end_datetime'])
                  ->where('end_datetime', '>', $validated['start_datetime']);
            })
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors([
                'schedule_conflict' => 'Jadwal bentrok dengan jadwal lain untuk pimpinan pujian dan pengkhotbah yang sama.'
            ]);
        }

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