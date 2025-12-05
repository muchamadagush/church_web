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
        // Handle AJAX request to check schedule count
        if (request()->has('check_count')) {
            $year = (int) request()->input('year');
            $month = (int) request()->input('month');
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();
            $count = VisitSchedule::whereBetween('start_datetime', [$monthStart, $monthEnd])->count();
            return response()->json(['count' => $count]);
        }

        $schedules = VisitSchedule::with('church')->orderBy('start_datetime')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $churches = Church::all();
    return view('worship-schedules.visits.index', compact('schedules', 'canEdit', 'canDelete', 'churches'));
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
            'month' => 'required|integer|min:1|max:12',
            'duration' => 'required|integer|min:30|max:480',
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Durasi tidak valid.');
        }

        // Kumpulkan semua hari Minggu pada bulan tersebut
        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();

        // Cek apakah bulan ini sudah memiliki 3 jadwal
        $monthStart = $firstDay->copy()->startOfMonth();
        $monthEnd = $lastDay->copy()->endOfMonth();
        $existingCount = VisitSchedule::whereBetween('start_datetime', [$monthStart, $monthEnd])->count();
        if ($existingCount >= 3) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Bulan ' . $month . '/' . $year . ' sudah memiliki 3 jadwal kunjungan. Tidak dapat membuat jadwal tambahan.');
        }

        $sundays = [];
        $cursor = $firstDay->copy();
        while ($cursor->lte($lastDay)) {
            if ($cursor->dayOfWeek === Carbon::SUNDAY) {
                // waktu mulai tetap jam 10:00
                $start = Carbon::create($year, $month, $cursor->day, 10, 0, 0);
                $end = $start->copy()->addMinutes($duration);
                $sundays[] = ['start' => $start, 'end' => $end];
            }
            $cursor->addDay();
        }

        if (count($sundays) < 3) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Jumlah hari Minggu pada bulan ini kurang dari 3.');
        }

        // Pilih 3 hari Minggu secara acak tanpa duplikasi tanggal
        shuffle($sundays);
        $selectedSlots = array_slice($sundays, 0, 3);

        // Ambil gereja secara acak, pastikan tidak ada gereja yang sama dalam satu bulan
        $churches = Church::orderBy('name', 'asc')->get();
        if ($churches->count() < 3) {
            return redirect()->route('worship-schedules.visits.index')
                ->with('error', 'Jumlah gereja kurang dari 3 untuk penjadwalan bulan ini.');
        }
        $churchIds = $churches->pluck('id')->all();
        shuffle($churchIds);
        $selectedChurchIds = array_slice($churchIds, 0, 3);

        // Validasi: pastikan bulan tersebut belum memiliki jadwal kunjungan bertabrakan pada jam yang sama
        $created = 0;
        foreach ($selectedSlots as $i => $slot) {
            $startDatetime = $slot['start'];
            $endDatetime = $slot['end'];
            $churchId = $selectedChurchIds[$i];

            // Cek overlap global (ketua wilayah tidak bisa di dua tempat bersamaan)
            $overlap = VisitSchedule::where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();

            // Cek apakah pada bulan yang sama sudah ada jadwal untuk gereja tersebut (hindari duplikasi gereja dalam bulan)
            $churchDupInMonth = VisitSchedule::where('church_id', $churchId)
                ->whereBetween('start_datetime', [$monthStart, $monthEnd])
                ->exists();

            if (!$overlap && !$churchDupInMonth) {
                VisitSchedule::create([
                    'church_id' => $churchId,
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                ]);
                $created++;
            }
        }

        return redirect()->route('worship-schedules.visits.index')
            ->with('success', "Berhasil generate {$created} jadwal kunjungan bulan {$month}/{$year}.");
    }
}
