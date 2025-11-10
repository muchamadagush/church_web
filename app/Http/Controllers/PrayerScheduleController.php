<?php

namespace App\Http\Controllers;

use App\Models\PrayerSchedule;
use Illuminate\Http\Request;
use App\Models\Church;
use App\Helpers\PermissionHelper;
use Carbon\Carbon;

class PrayerScheduleController extends Controller
{
    public function index()
    {
        $schedules = PrayerSchedule::orderBy('start_datetime')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');

        $today = Carbon::today();
        $hasTodaySchedules = PrayerSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();
        return view('worship-schedules.prayer-schedules.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules'));
    }

    public function create()
    {
        $churches = Church::all();
        return view('worship-schedules.prayer-schedules.form', compact('churches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'nama_gereja' => 'required|string',
            'pimpinan_pujian' => 'required|string',
            'pengkhotbah' => 'required|string',
        ]);

        // Check for schedule conflicts
        $conflictingSchedule = PrayerSchedule::where(function($query) use ($request) {
            $query->where(function($q) use ($request) {
                $q->where('start_datetime', '<', $request->end_datetime)
                  ->where('end_datetime', '>', $request->start_datetime);
            })->where(function($q) use ($request) {
                $q->where('pimpinan_pujian', $request->pimpinan_pujian)
                  ->orWhere('pengkhotbah', $request->pengkhotbah);
            });
        })->first();

        if ($conflictingSchedule) {
            return back()
                ->withInput()
                ->withErrors(['schedule_conflict' => 'Pimpinan pujian atau pengkhotbah sudah memiliki jadwal di waktu yang sama.']);
        }

        PrayerSchedule::create($validated);
        return redirect()->route('worship-schedules.prayer-schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(PrayerSchedule $prayer_schedule)
    {
        $churches = Church::all();
        return view('worship-schedules.prayer-schedules.form', compact('prayer_schedule', 'churches'));
    }

    public function update(Request $request, PrayerSchedule $prayer_schedule)
    {
        $validated = $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'nama_gereja' => 'required|string',
            'pimpinan_pujian' => 'required|string',
            'pengkhotbah' => 'required|string',
        ]);

        // Check for schedule conflicts excluding current schedule
        $conflictingSchedule = PrayerSchedule::where('id', '!=', $prayer_schedule->id)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('start_datetime', '<', $request->end_datetime)
                      ->where('end_datetime', '>', $request->start_datetime);
                })->where(function($q) use ($request) {
                    $q->where('pimpinan_pujian', $request->pimpinan_pujian)
                      ->orWhere('pengkhotbah', $request->pengkhotbah);
                });
            })->first();

        if ($conflictingSchedule) {
            return back()
                ->withInput()
                ->withErrors(['schedule_conflict' => 'Pimpinan pujian atau pengkhotbah sudah memiliki jadwal di waktu yang sama.']);
        }

        $prayer_schedule->update($validated);
        return redirect()->route('worship-schedules.prayer-schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy(PrayerSchedule $prayer_schedule)
    {
        try {
            $prayer_schedule->delete();
            return redirect()->route('worship-schedules.prayer-schedules.index')->with('success', 'Jadwal berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('worship-schedules.prayer-schedules.index')->with('error', 'Gagal menghapus jadwal');
        }
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
        $existingCount = PrayerSchedule::whereBetween('start_datetime', [$date, $dateEnd])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.prayer-schedules.index')
                ->with('error', 'Tanggal tersebut sudah memiliki jadwal. Generate hanya untuk hari yang masih kosong.');
        }

        // Get all churches from nama_gereja field (assuming it's stored as church names)
        // Since PrayerSchedule uses nama_gereja as a string field, we'll create one schedule per request
        // Or we can get churches from the Church model
        $churches = \App\Models\Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.prayer-schedules.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // Greedy algorithm: Schedule churches sequentially with breaks
        $currentTime = Carbon::parse($date->format('Y-m-d') . ' 09:00');
        $duration = (int) $validated['duration'];
        if ($duration <= 0) {
            return redirect()->route('worship-schedules.prayer-schedules.index')
                ->with('error', 'Durasi tidak valid.');
        }
        $breakMinutes = 15;
        $created = 0;

        foreach ($churches as $church) {
            $startDatetime = $currentTime->copy();
            $endDatetime = $startDatetime->copy()->addMinutes((int) $duration);

            // Check overlap before creating
            $overlap = PrayerSchedule::where('start_datetime', '<', $endDatetime)
                ->where('end_datetime', '>', $startDatetime)
                ->exists();

            if (!$overlap) {
                PrayerSchedule::create([
                    'nama_gereja' => $church->name,
                    'pimpinan_pujian' => 'Pimpinan Pujian ' . $church->name,
                    'pengkhotbah' => 'Pengkhotbah ' . $church->name,
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                ]);
                $created++;
            }

            // Move to next time slot
            $currentTime->addMinutes((int) $duration + $breakMinutes);
        }

        return redirect()->route('worship-schedules.prayer-schedules.index')
            ->with('success', "Berhasil generate {$created} jadwal doa.");
    }
}
