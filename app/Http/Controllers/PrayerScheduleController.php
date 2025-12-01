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
            'end_datetime' => 'required|date',
            'nama_gereja' => 'required|string',
            'pimpinan_pujian' => 'required|string',
            'pengkhotbah' => 'required|string',
        ]);

        // Validate end_datetime is after start_datetime
        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);
        if ($end->lte($start)) {
            return back()->withInput()->withErrors([
                'end_datetime' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
        }

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
            'end_datetime' => 'required|date',
            'nama_gereja' => 'required|string',
            'pimpinan_pujian' => 'required|string',
            'pengkhotbah' => 'required|string',
        ]);

        // Validate end_datetime is after start_datetime
        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);
        if ($end->lte($start)) {
            return back()->withInput()->withErrors([
                'end_datetime' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
        }

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
     * Generate schedules for prayer - one schedule per month on Friday.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'duration' => 'required|integer|min:30|max:480',
        ]);

        $year = (int) $validated['year'];
        $duration = (int) $validated['duration'];

        // Check if schedules already exist for this year
        $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
        $endOfYear = $startOfYear->copy()->endOfYear();
        
        $existingCount = PrayerSchedule::whereBetween('start_datetime', [$startOfYear, $endOfYear])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.prayer-schedules.index')
                ->with('error', 'Tahun tersebut sudah memiliki jadwal. Generate hanya untuk tahun yang masih kosong.');
        }

        // Get all churches
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.prayer-schedules.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // List of worship leaders (pimpin pujian) - 11 unique names
        $worshipLeaders = [
            'HT. Sahra Pamo',
            'Ibu Rosniaty Desy',
            'Ibu Yuni Datu Maling',
            'Ibu Rina Tappi',
            'Ibu Dina Kondo',
            'Ibu Yushtin Lope\'',
            'Ibu Alfrida Samulang',
            'Ibu Banne Rara\'',
            'Ibu Ludia Patoding',
            'Ibu Alfrida Bunga',
            'Ibu Yuni Datu Maling', // 11th entry
        ];

        // List of speakers (pengkhotbah) - 11 unique names
        $speakers = [
            'Pdt. Frits Natun, S.Th',
            'Pdm. Yahya Batto\'',
            'Pdm. Andarias Minggu',
            'Pdt. Daniel Johni S.Th',
            'Pdm. Mesakh Bennu, S.Th',
            'Pdt. Frits Natun, S.Th',
            'Ibu Yuni Datu Maling',
            'Ibu Rina Tappi\'',
            'Ibu Sahra Pamo',
            'Pdt. Daniel Johni S.Th',
            'Pdm. Yahya Batto\'', // 11th entry
        ];

        // Create assignments for 12 months ensuring all 11 names get at least 1 schedule
        $worshipLeaderAssignments = $worshipLeaders; // Use all 11
        $speakerAssignments = $speakers; // Use all 11
        
        // Add one more random selection for the 12th month
        $worshipLeaderAssignments[] = $worshipLeaders[array_rand($worshipLeaders)];
        $speakerAssignments[] = $speakers[array_rand($speakers)];
        
        // Shuffle to randomize order
        shuffle($worshipLeaderAssignments);
        shuffle($speakerAssignments);

        $created = 0;
        
        // Generate 12 schedules (one per month) - all on Friday
        for ($month = 1; $month <= 12; $month++) {
            // Find all Fridays in this month
            $firstDayOfMonth = Carbon::create($year, $month, 1);
            $fridays = [];
            $currentDate = $firstDayOfMonth->copy();
            
            // Find first Friday
            while ($currentDate->dayOfWeek !== Carbon::FRIDAY && $currentDate->month == $month) {
                $currentDate->addDay();
            }
            
            // Collect all Fridays in the month
            while ($currentDate->month == $month) {
                $fridays[] = $currentDate->day;
                $currentDate->addWeek();
            }
            
            // Random Friday from available Fridays
            $dayOfMonth = !empty($fridays) ? $fridays[array_rand($fridays)] : 1;
            
            // Fixed hour: 10:00 AM
            $hour = 10;
            
            $startDatetime = Carbon::create($year, $month, $dayOfMonth, $hour, 0, 0);
            $endDatetime = $startDatetime->copy()->addMinutes($duration);

            // Select random church
            $church = $churches->random();
            
            // Assign worship leader and speaker from shuffled assignments (ensures each gets at least 1)
            $worshipLeader = $worshipLeaderAssignments[$month - 1];
            $speaker = $speakerAssignments[$month - 1];

            PrayerSchedule::create([
                'nama_gereja' => $church->name,
                'pimpinan_pujian' => $worshipLeader,
                'pengkhotbah' => $speaker,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
            ]);
            $created++;
        }

        return redirect()->route('worship-schedules.prayer-schedules.index')
            ->with('success', "Berhasil generate {$created} jadwal doa wilayah untuk tahun {$year}.");
    }
}
