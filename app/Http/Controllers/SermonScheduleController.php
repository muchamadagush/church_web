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
        $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $monthNumber = array_search($monthName, $monthNames) + 1;
        
        $date = Carbon::create($year, $monthNumber, 1);
        $lastDayOfMonth = $date->endOfMonth();
        
        // Find the last Sunday
        $lastSunday = $lastDayOfMonth->copy();
        while ($lastSunday->dayOfWeek !== Carbon::SUNDAY) {
            $lastSunday->subDay();
        }
        
        return [
            'start' => $lastSunday->copy()->setTime(10, 0, 0),
            'end' => $lastSunday->copy()->setTime(12, 0, 0),
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

        try {
            // Check if schedules exist for this year
            $existingCount = SermonSchedule::whereYear('start_datetime', $year)->count();
            if ($existingCount > 0) {
                return redirect()
                    ->route('worship-schedules.sermons.index')
                    ->with('error', "Jadwal untuk tahun {$year} sudah ada. Hapus jadwal tahun tersebut terlebih dahulu untuk membuat jadwal baru.");
            }

            $preachers = [
                ['name' => 'Pdt. DANIEL JOHNI, S.Th', 'home_church' => 'GGP BUKIT ZAITUN KOLE'],
                ['name' => 'Pdt. ANDARIAS LAYUK LANGI\', S.Th', 'home_church' => 'GGP SALUREA'],
                ['name' => 'Pdp. SAHRA PAMO', 'home_church' => 'GGP PA\'KAPPAN'],
                ['name' => 'Pdm. ANDARIAS MINGGU', 'home_church' => 'GGP GETSEMANI BU\'BUK'],
                ['name' => 'Pdm. MESAKH BENNU, S.Th', 'home_church' => 'GGP LEMBAH PUJIAN TO\' LEMO'],
                ['name' => 'Pdt. ORVA, S.Pd', 'home_church' => 'GGP SHALOM NE\'ME\'SE'],
                ['name' => 'Pdm. MATIUS LEPPANG', 'home_church' => 'SOLAGRATIA TIROAN'],
                ['name' => 'PdP. YUNI DATU MALING', 'home_church' => 'GGP EL-SHADDAI RATTE'],
                ['name' => 'Pdp. RINA TAPPI\'', 'home_church' => 'GGP IMANUEL RATTE'],
                ['name' => 'Pdm. THOMAS TAPPI', 'home_church' => 'GGP BENTENG BATU'],
                ['name' => 'Pdt. SEMUEL SONI, S.Pd', 'home_church' => 'GGP ANUGRAH SALU BARUPPU\''],
            ];

            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $churches = Church::all();
            $numPreachers = count($preachers);
            $numChurches = $churches->count();

            // Validation: Ensure we have enough churches for preachers to rotate
            if ($numChurches < 2) {
                return redirect()
                    ->route('worship-schedules.sermons.index')
                    ->with('error', 'Minimal 2 gereja diperlukan untuk rotasi pengkhotbah.');
            }

            DB::beginTransaction();

            $totalSchedules = 0;

            // For each month, assign preachers to random churches (not their home church)
            foreach ($months as $monthIndex => $month) {
                // Calculate last Sunday of the month for the given year
                $datetime = $this->calculateLastSunday($month, $year);

                // Shuffle preachers for randomization
                $shuffledPreachers = collect($preachers)->shuffle();

                // Assign each preacher to a random church that's not their home church
                foreach ($shuffledPreachers as $preacher) {
                    // Get churches excluding home church
                    $availableChurches = $churches->filter(function($church) use ($preacher) {
                        return $church->name !== $preacher['home_church'];
                    });

                    if ($availableChurches->isEmpty()) {
                        // If no other churches available, skip this preacher for this month
                        continue;
                    }

                    // Select random church
                    $selectedChurch = $availableChurches->random();

                    // Create schedule
                    SermonSchedule::create([
                        'pengkhotbah' => $preacher['name'],
                        'church_id' => $selectedChurch->id,
                        'month' => $month,
                        'start_datetime' => $datetime['start'],
                        'end_datetime' => $datetime['end'],
                    ]);

                    $totalSchedules++;
                }
            }

            DB::commit();

            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', "Jadwal pertukaran khotbah untuk tahun {$year} berhasil dibuat. Total {$totalSchedules} jadwal dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error generating sermon schedules: ' . $e->getMessage());
            
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }
}