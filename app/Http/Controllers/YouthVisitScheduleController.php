<?php

namespace App\Http\Controllers;

use App\Models\YouthVisitSchedule;
use App\Models\Church;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\PermissionHelper;

class YouthVisitScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = YouthVisitSchedule::with('church')
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        $churches = Church::orderBy('name', 'asc')->get();
        $today = Carbon::today();
        $hasTodaySchedules = YouthVisitSchedule::whereBetween('start_datetime', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->exists();
        return view('worship-schedules.youth-visit.index', compact('schedules', 'canEdit', 'canDelete', 'hasTodaySchedules', 'churches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.youth-visit.create', compact('churches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date',
            'church_id' => 'required|exists:churches,id',
            'worship_leader' => 'required|string|max:255|different:speaker',
            'speaker' => 'required|string|max:255|different:worship_leader',
        ]);

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        // Validate end_datetime is after start_datetime
        if ($end->lte($start)) {
            return back()->withInput()->withErrors([
                'end_datetime' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
        }

        // Prevent double-booking the same person in any role within overlapping time
        $personConflict = YouthVisitSchedule::where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->where(function ($q) use ($request) {
                $q->where('worship_leader', $request->worship_leader)
                  ->orWhere('speaker', $request->worship_leader)
                  ->orWhere('worship_leader', $request->speaker)
                  ->orWhere('speaker', $request->speaker);
            })
            ->exists();

        if ($personConflict) {
            return back()->withInput()->withErrors([
                'worship_leader' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
                'speaker' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
            ]);
        }

        YouthVisitSchedule::create([
            'start_datetime' => $start,
            'end_datetime' => $end,
            'church_id' => $request->church_id,
            'worship_leader' => $request->worship_leader,
            'speaker' => $request->speaker,
        ]);

        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', 'Tambah Jadwal Kunjungan Kaum Muda Berhasil');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\YouthVisitSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(YouthVisitSchedule $schedule)
    {
        $churches = Church::orderBy('name', 'asc')->get();
        return view('worship-schedules.youth-visit.edit', compact('schedule', 'churches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\YouthVisitSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, YouthVisitSchedule $schedule)
    {
        $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date',
            'church_id' => 'required|exists:churches,id',
            'worship_leader' => 'required|string|max:255|different:speaker',
            'speaker' => 'required|string|max:255|different:worship_leader',
        ]);

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        // Validate end_datetime is after start_datetime
        if ($end->lte($start)) {
            return back()->withInput()->withErrors([
                'end_datetime' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
        }

        // Prevent double-booking for overlapping time on update (exclude self)
        $personConflict = YouthVisitSchedule::where('id', '!=', $schedule->id)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->where(function ($q) use ($request) {
                $q->where('worship_leader', $request->worship_leader)
                  ->orWhere('speaker', $request->worship_leader)
                  ->orWhere('worship_leader', $request->speaker)
                  ->orWhere('speaker', $request->speaker);
            })
            ->exists();

        if ($personConflict) {
            return back()->withInput()->withErrors([
                'worship_leader' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
                'speaker' => 'Orang yang dipilih sudah terjadwal pada waktu tersebut.',
            ]);
        }

        $schedule->update([
            'start_datetime' => $start,
            'end_datetime' => $end,
            'church_id' => $request->church_id,
            'worship_leader' => $request->worship_leader,
            'speaker' => $request->speaker,
        ]);

        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', 'Update Jadwal Kunjungan Kaum Muda Berhasil');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\YouthVisitSchedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(YouthVisitSchedule $schedule)
    {
        $schedule->delete();
        
        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', 'Hapus Jadwal Kunjungan Kaum Muda Berhasil');
    }

    /**
     * Generate schedules for youth visit - one schedule per month for the whole year.
     * Each month gets one church in rotation.
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
        
        $existingCount = YouthVisitSchedule::whereBetween('start_datetime', [$startOfYear, $endOfYear])->count();
        if ($existingCount > 0) {
            return redirect()->route('worship-schedules.youth-visit.index')
                ->with('error', 'Tahun tersebut sudah memiliki jadwal. Generate hanya untuk tahun yang masih kosong.');
        }

        // Get all churches
        $churches = Church::orderBy('name', 'asc')->get();
        
        if ($churches->isEmpty()) {
            return redirect()->route('worship-schedules.youth-visit.index')
                ->with('error', 'Tidak ada gereja yang tersedia.');
        }

        // List of speakers (pengkhotbah) to rotate
        $speakers = [
            'Pdm. YAHYA BATTO\'',
            'Pdp. SAHRINA S.Pd',
            'Pdp. VIVI TAPPI\'',
            'HT. ROSNIATI DESI',
            'Pdt. DANIEL JOHNI, S.Th',
            'HT. SARA PAMO',
            'Pdm. YOHANA TUNTUN, S.Th',
            'Pdm. ANDARIAS MINGGU',
            'Pdp. ALFRIDA SAMULANG',
            'Pdm. KEPPI LOPU\'',
            'Pdm. MESAKH BENNU, S.Th',
        ];

        // Create church assignment for 12 months
        // First, ensure each church gets at least one month
        $churchAssignments = [];
        $churchesArray = $churches->toArray();
        
        // Assign each church to at least one month
        foreach ($churchesArray as $index => $church) {
            if ($index < 12) { // Only if month available
                $churchAssignments[] = $church;
            }
        }
        
        // Fill remaining months with random churches
        while (count($churchAssignments) < 12) {
            $churchAssignments[] = $churchesArray[array_rand($churchesArray)];
        }
        
        // Shuffle to randomize order while keeping all churches represented
        shuffle($churchAssignments);

        $created = 0;
        
        // Generate 12 schedules (one per month)
        for ($month = 1; $month <= 12; $month++) {
            // Get church for this month from shuffled assignments
            $churchData = $churchAssignments[$month - 1];
            
            // Find all Sundays in this month
            $firstDayOfMonth = Carbon::create($year, $month, 1);
            $sundays = [];
            $currentDate = $firstDayOfMonth->copy();
            
            // Find first Sunday
            while ($currentDate->dayOfWeek !== Carbon::SUNDAY && $currentDate->month == $month) {
                $currentDate->addDay();
            }
            
            // Collect all Sundays in the month
            while ($currentDate->month == $month) {
                $sundays[] = $currentDate->day;
                $currentDate->addWeek();
            }
            
            // Random Sunday from available Sundays (prefer week 2-3)
            $dayOfMonth = !empty($sundays) ? $sundays[array_rand($sundays)] : 1;
            
            // Random hour: 09:00 or 13:00
            $hour = (rand(0, 1) == 0) ? 9 : 13;
            
            $startDatetime = Carbon::create($year, $month, $dayOfMonth, $hour, 0, 0);
            $endDatetime = $startDatetime->copy()->addMinutes($duration);

            // Worship leader: KM {church name}
            $worshipLeader = 'KM ' . $churchData['name'];
            
            // Speaker: select randomly from the list
            $speaker = $speakers[array_rand($speakers)];

            YouthVisitSchedule::create([
                'church_id' => $churchData['id'],
                'worship_leader' => $worshipLeader,
                'speaker' => $speaker,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
            ]);
            $created++;
        }

        return redirect()->route('worship-schedules.youth-visit.index')
            ->with('success', "Berhasil generate {$created} jadwal kunjungan kaum muda untuk tahun {$year}.");
    }
}
