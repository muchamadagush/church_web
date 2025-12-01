<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Church;
use App\Models\SermonSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SermonScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = SermonSchedule::with('church')
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $churches = Church::orderBy('name', 'asc')->get();
        $today = Carbon::today();
        $hasTodaySchedules = SermonSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();

        return view('worship-schedules.sermons.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules', 'churches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.sermons.create', compact('churches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'church_id' => 'required|exists:churches,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ], [
            'pengkhotbah.required' => 'Pengkhotbah wajib diisi.',
            'church_id.required' => 'Gereja wajib dipilih.',
            'start_datetime.required' => 'Waktu mulai wajib diisi.',
            'end_datetime.required' => 'Waktu selesai wajib diisi.',
            'end_datetime.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            $start = Carbon::parse($validated['start_datetime']);
            $end = Carbon::parse($validated['end_datetime']);
        } catch (\Exception $e) {
            return back()->withErrors(['start_datetime' => 'Format datetime tidak valid'])->withInput();
        }

        if ($end->lessThanOrEqualTo($start)) {
            return back()->withErrors(['end_datetime' => 'Waktu selesai harus lebih besar daripada waktu mulai'])->withInput();
        }

        // Global overlap check against existing flattened sermon schedules
        // Check if the same pengkhotbah is already scheduled at overlapping time
        $overlap = SermonSchedule::query()
            ->where('pengkhotbah', $validated['pengkhotbah'])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        if ($overlap) {
            return back()->withErrors(['pengkhotbah' => 'Pengkhotbah ini sudah dijadwalkan pada waktu yang bertumpuk.'])->withInput();
        }

        SermonSchedule::create([
            'pengkhotbah' => $validated['pengkhotbah'],
            'church_id' => $validated['church_id'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
        ]);

        return redirect()->route('worship-schedules.sermons.index')->with('success', 'Jadwal khotbah berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SermonSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(SermonSchedule $schedule)
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.sermons.edit', compact('schedule', 'churches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SermonSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SermonSchedule $schedule)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'church_id' => 'required|exists:churches,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ], [
            'pengkhotbah.required' => 'Pengkhotbah wajib diisi.',
            'church_id.required' => 'Gereja wajib dipilih.',
            'start_datetime.required' => 'Waktu mulai wajib diisi.',
            'end_datetime.required' => 'Waktu selesai wajib diisi.',
            'end_datetime.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            $start = Carbon::parse($validated['start_datetime']);
            $end = Carbon::parse($validated['end_datetime']);
        } catch (\Exception $e) {
            return back()->withErrors(['start_datetime' => 'Format datetime tidak valid'])->withInput();
        }

        if ($end->lessThanOrEqualTo($start)) {
            return back()->withErrors(['end_datetime' => 'Waktu selesai harus lebih besar daripada waktu mulai'])->withInput();
        }

        $overlap = SermonSchedule::query()
            ->where('id', '!=', $schedule->id)
            ->where('pengkhotbah', $validated['pengkhotbah'])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        if ($overlap) {
            return back()->withErrors(['pengkhotbah' => 'Pengkhotbah ini sudah dijadwalkan pada waktu yang bertumpuk.'])->withInput();
        }

        $schedule->update([
            'pengkhotbah' => $validated['pengkhotbah'],
            'church_id' => $validated['church_id'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
        ]);

        return redirect()->route('worship-schedules.sermons.index')->with('success', 'Jadwal khotbah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SermonSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(SermonSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('worship-schedules.sermons.index')
                        ->with('success', 'Jadwal khotbah berhasil dihapus');
    }

    /**
     * Generate sermon exchange schedule - one per month on last Sunday
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
        
        $existingCount = SermonSchedule::whereBetween('start_datetime', [$startOfYear, $endOfYear])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.sermons.index')
                ->with('error', 'Tahun tersebut sudah memiliki jadwal. Generate hanya untuk tahun yang masih kosong.');
        }

        // Get all churches (11 churches)
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->count() < 11) {
            return redirect()->route('worship-schedules.sermons.index')
                ->with('error', 'Harus ada minimal 11 gereja untuk generate jadwal.');
        }

        // List of 11 preachers (pengkhotbah) with their home church
        $preachers = [
            ['name' => 'Pdt. DANIEL JOHNI, S.Th', 'home_church' => 'GGP SALUREA'],
            ['name' => 'Pdm. ANDARIAS MINGGU', 'home_church' => 'GGP PA\'KAPPAN'],
            ['name' => 'Pdp. SAHRA PAMO', 'home_church' => 'GGP LEMBAH PUJIAN TO\'LEMO'],
            ['name' => 'Pdm. YAHYA BATTO\'', 'home_church' => 'GGP SHALOM NE\'ME\'SE'],
            ['name' => 'Pdt. FRITS NATUN, S.Th', 'home_church' => 'GGP SOLAGRATIA TIROAN'],
            ['name' => 'Pdt. DRIVA, S.Pd', 'home_church' => 'GGP EL SHADDAI RATTE'],
            ['name' => 'Pdm. ELISA LIMBONG', 'home_church' => 'GGP IMANUEL RATTE'],
            ['name' => 'Pdp. YUNI DATU MALING', 'home_church' => 'GGP BENTENG BATU'],
            ['name' => 'Pdp. RINA TAPPI', 'home_church' => 'GGP PA\'KAPPAN'],
            ['name' => 'Pdm. THOMAS TAPPI', 'home_church' => 'GGP ANUGRAH SALU BARUPPU\''],
            ['name' => 'Pdt. SELESTIN K, S.Pd', 'home_church' => 'GGP BUKIT ZAITUN KOLE'],
        ];

        // Create church-preacher assignments for 12 months
        // Each preacher must get at least 1 schedule
        $assignments = [];
        
        // First 11 months: assign each preacher once
        $shuffledPreachers = $preachers;
        shuffle($shuffledPreachers);
        
        foreach ($shuffledPreachers as $index => $preacher) {
            if ($index < 11) {
                $assignments[] = $preacher;
            }
        }
        
        // 12th month: random preacher
        $assignments[] = $preachers[array_rand($preachers)];
        
        // Shuffle assignments to randomize months
        shuffle($assignments);

        $created = 0;
        
        // Generate 12 schedules (one per month on last Sunday)
        for ($month = 1; $month <= 12; $month++) {
            // Find last Sunday of the month
            $lastDayOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
            
            // Go backwards to find last Sunday
            $currentDate = $lastDayOfMonth->copy();
            while ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                $currentDate->subDay();
            }
            
            $lastSunday = $currentDate->day;
            
            // Fixed hour: 10:00 AM
            $hour = 10;
            
            $startDatetime = Carbon::create($year, $month, $lastSunday, $hour, 0, 0);
            $endDatetime = $startDatetime->copy()->addMinutes($duration);

            // Get preacher for this month
            $preacher = $assignments[$month - 1];
            
            // Find a church that is NOT the preacher's home church
            $availableChurches = $churches->filter(function($church) use ($preacher) {
                return $church->name !== $preacher['home_church'];
            });
            
            if ($availableChurches->isEmpty()) {
                // Fallback: use any church
                $selectedChurch = $churches->random();
            } else {
                $selectedChurch = $availableChurches->random();
            }

            SermonSchedule::create([
                'pengkhotbah' => $preacher['name'],
                'church_id' => $selectedChurch->id,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
            ]);
            $created++;
        }

        return redirect()->route('worship-schedules.sermons.index')
            ->with('success', "Berhasil generate {$created} jadwal pertukaran khotbah untuk tahun {$year}.");
    }
}