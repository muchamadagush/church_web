<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\WomenVisitSchedule;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WomenVisitScheduleController extends Controller
{
    public function index()
    {
        $schedules = WomenVisitSchedule::with('church')->orderBy('start_datetime')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $today = Carbon::today();
        $hasTodaySchedules = WomenVisitSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();
        return view('worship-schedules.women-visits.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules' ));
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
        $existingCount = WomenVisitSchedule::whereBetween('start_datetime', [$date, $dateEnd])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.women-visits.index')
                ->with('error', 'Tanggal tersebut sudah memiliki jadwal. Generate hanya untuk hari yang masih kosong.');
        }

        // Get all churches
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.women-visits.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // Greedy algorithm: Schedule churches sequentially with breaks
    $currentTime = Carbon::parse($date->format('Y-m-d') . ' 09:00');
    $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.women-visits.index')
                ->with('error', 'Durasi tidak valid.');
        }
        $breakMinutes = 15;
        $created = 0;

        foreach ($churches as $church) {
            $startDatetime = $currentTime->copy();
            $endDatetime = $startDatetime->copy()->addMinutes((int) $duration);

            // Check overlap before creating
            $autoLeader = 'WL ' . $church->name;
            $autoPreacher = 'Pengkhotbah ' . $church->name;
            $overlap = WomenVisitSchedule::where(function($q) use ($autoLeader, $autoPreacher){
                    $q->where('worship_leader', $autoLeader)
                      ->orWhere('preacher', $autoPreacher);
                })
                ->where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();

            if (!$overlap) {
                WomenVisitSchedule::create([
                    'church_id' => $church->id,
                    'worship_leader' => $autoLeader,
                    'preacher' => $autoPreacher,
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                ]);
                $created++;
            }

            // Move to next time slot
            $currentTime->addMinutes((int) $duration + $breakMinutes);
        }

        return redirect()->route('worship-schedules.women-visits.index')
            ->with('success', "Berhasil generate {$created} jadwal kunjungan wanita.");
    }
}
