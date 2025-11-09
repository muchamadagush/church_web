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
}