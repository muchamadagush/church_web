<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\SermonSchedule;
use App\Models\SermonScheduleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\PermissionHelper;
use Carbon\Carbon;

class SermonScheduleController extends Controller
{
    /**
     * Calculate last Sunday of given month
     */
    private function calculateLastSunday($monthName, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $monthNumber = array_search($monthName, $monthNames) + 1;

        $date = Carbon::create($year, $monthNumber, 1);
        $lastSunday = $date->endOfMonth();
        while ($lastSunday->dayOfWeek !== Carbon::SUNDAY) {
            $lastSunday->subDay();
        }

        return [
            'start' => $lastSunday->copy()->setTime(10, 0, 0),
            'end'   => $lastSunday->copy()->setTime(12, 0, 0),
        ];
    }

    public function index(Request $request)
    {
        // Paginate all schedules without grouping
        $schedules = SermonSchedule::with('church')
            ->orderBy('start_datetime')
            ->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');

        return view('worship-schedules.sermons.index', compact('schedules', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $churches = Church::all();
        
        return view('worship-schedules.sermons.form', compact('churches'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'church_id' => 'required|exists:churches,id',
            'month' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate datetime based on month
            $datetime = $this->calculateLastSunday($validatedData['month']);
            
            SermonSchedule::create([
                'pengkhotbah' => $request->pengkhotbah,
                'church_id' => $request->church_id,
                'month' => $request->month,
                'start_datetime' => $datetime['start'],
                'end_datetime' => $datetime['end'],
            ]);

            DB::commit();

            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', 'Jadwal khotbah berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating sermon schedule: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    public function edit(SermonSchedule $schedule)
    {
        $churches = Church::all();
        
        return view('worship-schedules.sermons.form', compact('schedule', 'churches'));
    }

    public function update(Request $request, SermonSchedule $schedule)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'church_id' => 'required|exists:churches,id',
            'month' => 'required|string',
        ]);

        try {
            // Calculate datetime based on month
            $datetime = $this->calculateLastSunday($validated['month']);
            
            $schedule->update([
                'pengkhotbah' => $validated['pengkhotbah'],
                'church_id' => $validated['church_id'],
                'month' => $validated['month'],
                'start_datetime' => $datetime['start'],
                'end_datetime' => $datetime['end'],
            ]);

            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', 'Jadwal khotbah berhasil diperbarui');
        } catch (\Exception $e) {
            \Log::error('Error updating sermon schedule: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function destroy(SermonSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('worship-schedules.sermons.index')
                        ->with('success', 'Jadwal pertukaran khotbah berhasil dihapus');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $year = $request->year;

        // ❗ Cegah generate ulang
        if (SermonSchedule::whereYear('start_datetime', $year)->exists()) {
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('error', "Jadwal tahun {$year} sudah ada.");
        }

        $preachers = [
            ['name' => 'Pdt. DANIEL JOHNI, S.Th', 'home_church' => 'GGP BUKIT ZAITUN KOLE'],
            ['name' => 'Pdt. ANDARIAS LAYUK LANGI\', S.Th', 'home_church' => 'GGP SALUREA'],
            ['name' => 'Pdp. SAHRA PAMO', 'home_church' => 'GGP PA\'KAPPAN'],
            ['name' => 'Pdm. ANDARIAS MINGGU', 'home_church' => 'GGP GETSEMANI BU\'BUK'],
            ['name' => 'Pdm. MESAKH BENNU, S.Th', 'home_church' => 'GGP LEMBAH PUJIAN TO\' LEMO'],
            ['name' => 'Pdt. ORVA, S.Pd', 'home_church' => 'GGP SHALOM NE\'ME\'SE'],
            ['name' => 'Pdm. MATIUS LEPPANG', 'home_church' => 'SOLAGRATIA TIROAN'],
            ['name' => 'Pdp. YUNI DATU MALING', 'home_church' => 'GGP EL-SHADDAI RATTE'],
            ['name' => 'Pdp. RINA TAPPI\'', 'home_church' => 'GGP IMANUEL RATTE'],
            ['name' => 'Pdm. THOMAS TAPPI', 'home_church' => 'GGP BENTENG BATU'],
            ['name' => 'Pdt. SEMUEL SONI, S.Pd', 'home_church' => 'GGP ANUGRAH SALU BARUPPU\''],
        ];

        $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $churches = Church::all();

        DB::beginTransaction();

        try {
            /**
             * 🔒 LOCK dari database
             * Format: church_id|start_datetime => true
             */
            $lockedSlots = SermonSchedule::whereYear('start_datetime', $year)
                ->get()
                ->mapWithKeys(fn ($s) => [
                    $s->church_id . '|' . $s->start_datetime => true
                ])->toArray();

            $churchAssignmentsByMonth = [];
            $preacherChurchAssignments = [];
            $total = 0;

            foreach ($months as $month) {
                $datetime = $this->calculateLastSunday($month, $year);
                $churchAssignmentsByMonth[$month] = [];

                foreach (collect($preachers)->shuffle() as $preacher) {

                    $availableChurches = $churches->filter(function ($church) use (
                        $preacher,
                        $month,
                        $datetime,
                        $lockedSlots,
                        $churchAssignmentsByMonth,
                        $preacherChurchAssignments
                    ) {
                        if ($church->name === $preacher['home_church']) {
                            return false;
                        }

                        if (isset($preacherChurchAssignments[$preacher['name']][$church->id])) {
                            return false;
                        }

                        if (isset($churchAssignmentsByMonth[$month][$church->id])) {
                            return false;
                        }

                        $key = $church->id . '|' . $datetime['start'];
                        if (isset($lockedSlots[$key])) {
                            return false;
                        }

                        return true;
                    });

                    if ($availableChurches->isEmpty()) {
                        continue;
                    }

                    $selectedChurch = $availableChurches->random();

                    // ❗ Final safety check (DB)
                    $exists = SermonSchedule::where('church_id', $selectedChurch->id)
                        ->where('start_datetime', $datetime['start'])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    SermonSchedule::create([
                        'pengkhotbah' => $preacher['name'],
                        'church_id' => $selectedChurch->id,
                        'month' => $month,
                        'start_datetime' => $datetime['start'],
                        'end_datetime' => $datetime['end'],
                    ]);

                    $churchAssignmentsByMonth[$month][$selectedChurch->id] = true;
                    $preacherChurchAssignments[$preacher['name']][$selectedChurch->id] = true;
                    $lockedSlots[$selectedChurch->id . '|' . $datetime['start']] = true;

                    $total++;
                }
            }

            DB::commit();

            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', "Berhasil generate {$total} jadwal tahun {$year}");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('error', 'Gagal generate jadwal');
        }
    }
}