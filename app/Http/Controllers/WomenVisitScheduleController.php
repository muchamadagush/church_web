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
            'year' => 'required|integer|min:2024|max:2099',
            'duration' => 'required|integer|min:30|max:480',
        ]);

        $year = (int) $validated['year'];
        $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.women-visits.index')
                ->with('error', 'Durasi tidak valid.');
        }

        // Ambil semua gereja
        $churches = Church::orderBy('name', 'asc')->get();
        if ($churches->count() < 2) {
            return redirect()->route('worship-schedules.women-visits.index')
                ->with('error', 'Minimal 2 gereja diperlukan untuk generate jadwal.');
        }

        $worshipLeader = 'Jemaat Setempat';
        $created = 0;

        // Loop untuk 12 bulan
        for ($month = 1; $month <= 12; $month++) {
            // Cek apakah bulan ini sudah memiliki jadwal
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();
            $alreadyExists = WomenVisitSchedule::whereBetween('start_datetime', [$monthStart, $monthEnd])->exists();
            if ($alreadyExists) {
                continue; // Skip bulan yang sudah ada jadwal
            }

            // Kumpulkan semua hari Sabtu pada bulan tersebut
            $saturdays = [];
            $cursor = Carbon::create($year, $month, 1)->startOfDay();
            $lastDay = $cursor->copy()->endOfMonth();
            while ($cursor->lte($lastDay)) {
                if ($cursor->dayOfWeek === Carbon::SATURDAY) {
                    $start = Carbon::create($year, $month, $cursor->day, 10, 0, 0);
                    $end = $start->copy()->addMinutes($duration);
                    $saturdays[] = ['start' => $start, 'end' => $end];
                }
                $cursor->addDay();
            }

            if (empty($saturdays)) {
                continue; // Skip jika tidak ada hari Sabtu
            }

            // Pilih 1 Sabtu secara acak
            shuffle($saturdays);
            $slot = $saturdays[0];
            $startDatetime = $slot['start'];
            $endDatetime = $slot['end'];

            // Pilih tempat pelayanan acak
            $venueChurch = $churches->shuffle()->first();

            // Pilih pengkhotbah dari gereja lain (bukan gereja tempat ibadah)
            $otherChurches = $churches->where('id', '!=', $venueChurch->id);
            if ($otherChurches->isEmpty()) {
                continue; // Skip jika tidak ada gereja lain
            }
            $preacherChurch = $otherChurches->shuffle()->first();
            $preacher = 'Jemaat ' . $preacherChurch->name;

            // Cek overlap global
            $overlap = WomenVisitSchedule::where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();
            if ($overlap) {
                continue; // Skip jika ada overlap
            }

            // Buat jadwal
            WomenVisitSchedule::create([
                'church_id' => $venueChurch->id,
                'worship_leader' => $worshipLeader,
                'preacher' => $preacher,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
            ]);
            $created++;
        }

        return redirect()->route('worship-schedules.women-visits.index')
            ->with('success', "Berhasil generate {$created} jadwal ibadah kaum wanita untuk tahun {$year}.");
    }
}
