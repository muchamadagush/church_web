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

    public function index()
    {
        // Define month order for sorting
        $monthOrder = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        // Group schedules by pengkhotbah
        $schedules = SermonSchedule::with('church')
            ->orderBy('pengkhotbah')
            ->get()
            ->groupBy('pengkhotbah')
            ->map(function($preacherSchedules) use ($monthOrder) {
                // Sort each preacher's schedules by month order
                return $preacherSchedules->sortBy(function($schedule) use ($monthOrder) {
                    return array_search($schedule->month, $monthOrder);
                });
            });

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        
        return view('worship-schedules.sermons.index', compact('schedules', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $churches = Church::all();
        
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
        
        return view('worship-schedules.sermons.form', compact('churches', 'preachers'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'pengkhotbah' => 'required|string|max:255',
                'schedules' => 'required|array|min:1',
                'schedules.*.church_id' => 'required|exists:churches,id',
                'schedules.*.month' => 'required|string',
            ]);

            DB::beginTransaction();

            // Create multiple schedules with the same pengkhotbah
            foreach ($request->schedules as $scheduleData) {
                // Calculate datetime based on month
                $datetime = $this->calculateLastSunday($scheduleData['month']);
                
                SermonSchedule::create([
                    'pengkhotbah' => $request->pengkhotbah,
                    'church_id' => $scheduleData['church_id'],
                    'month' => $scheduleData['month'],
                    'start_datetime' => $datetime['start'],
                    'end_datetime' => $datetime['end'],
                ]);
            }

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
        
        return view('worship-schedules.sermons.form', compact('schedule', 'churches', 'preachers'));
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

    public function editPreacher($pengkhotbah)
    {
        $pengkhotbah = urldecode($pengkhotbah);
        $schedules = SermonSchedule::where('pengkhotbah', $pengkhotbah)->get();
        
        if ($schedules->isEmpty()) {
            return redirect()->route('worship-schedules.sermons.index')
                ->with('error', 'Pengkhotbah tidak ditemukan');
        }
        
        $churches = Church::all();
        
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
        
        return view('worship-schedules.sermons.edit-preacher', compact('schedules', 'pengkhotbah', 'churches', 'preachers'));
    }

    public function updatePreacher(Request $request, $pengkhotbah)
    {
        $pengkhotbah = urldecode($pengkhotbah);
        
        try {
            $validatedData = $request->validate([
                'pengkhotbah' => 'required|string|max:255',
                'schedules' => 'required|array|min:1',
                'schedules.*.church_id' => 'required|exists:churches,id',
                'schedules.*.month' => 'required|string',
            ]);

            DB::beginTransaction();

            // Delete all old schedules for this pengkhotbah
            SermonSchedule::where('pengkhotbah', $pengkhotbah)->delete();

            // Create new schedules
            foreach ($request->schedules as $scheduleData) {
                // Calculate datetime based on month
                $datetime = $this->calculateLastSunday($scheduleData['month']);
                
                SermonSchedule::create([
                    'pengkhotbah' => $request->pengkhotbah,
                    'church_id' => $scheduleData['church_id'],
                    'month' => $scheduleData['month'],
                    'start_datetime' => $datetime['start'],
                    'end_datetime' => $datetime['end'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', 'Jadwal khotbah berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating preacher schedules: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function destroyPreacher($pengkhotbah)
    {
        $pengkhotbah = urldecode($pengkhotbah);
        
        try {
            $deleted = SermonSchedule::where('pengkhotbah', $pengkhotbah)->delete();
            
            if ($deleted > 0) {
                return redirect()->route('worship-schedules.sermons.index')
                    ->with('success', "Semua jadwal untuk {$pengkhotbah} berhasil dihapus");
            } else {
                return redirect()->route('worship-schedules.sermons.index')
                    ->with('error', 'Pengkhotbah tidak ditemukan');
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting preacher schedules: ' . $e->getMessage());
            return redirect()->route('worship-schedules.sermons.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function generate(Request $request)
    {
        try {
            // Check if any schedules exist
            $existingCount = SermonSchedule::count();
            if ($existingCount > 0) {
                return redirect()
                    ->route('worship-schedules.sermons.index')
                    ->with('error', 'Jadwal sudah ada. Hapus semua jadwal terlebih dahulu untuk membuat jadwal baru.');
            }

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

            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $churches = Church::all();
            $numPreachers = count($preachers);
            $numChurches = $churches->count();
            $numMonths = count($months);

            // Validation: Check if we have enough months for distribution
            if ($numMonths < $numChurches) {
                return redirect()
                    ->route('worship-schedules.sermons.index')
                    ->with('error', 'Jumlah bulan harus lebih besar atau sama dengan jumlah gereja untuk distribusi yang merata.');
            }

            DB::beginTransaction();

            // Algorithm: Distribute months to churches for each preacher
            // Create a rotation schedule to avoid conflicts
            $churchMonthMatrix = []; // Store [church_id][month] assignments

            // For each preacher, assign months to churches
            foreach ($preachers as $preacherIndex => $preacher) {
                // Calculate offset for this preacher to ensure different month distributions
                $offset = ($preacherIndex * $numChurches) % $numMonths;

                // For each church, assign a different month
                foreach ($churches as $churchIndex => $church) {
                    // Calculate month index with rotation
                    $monthIndex = ($offset + $churchIndex) % $numMonths;
                    $month = $months[$monthIndex];

                    // Calculate datetime based on month using helper method
                    $datetime = $this->calculateLastSunday($month);

                    // Check for conflict: same church-month combination shouldn't exist already
                    if (!isset($churchMonthMatrix[$church->id][$monthIndex])) {
                        $churchMonthMatrix[$church->id][$monthIndex] = [];
                    }
                    $churchMonthMatrix[$church->id][$monthIndex][] = $preacher['name'];

                    // Create schedule
                    SermonSchedule::create([
                        'pengkhotbah' => $preacher['name'],
                        'church_id' => $church->id,
                        'month' => $month,
                        'start_datetime' => $datetime['start'],
                        'end_datetime' => $datetime['end'],
                    ]);
                }
            }

            DB::commit();

            $totalSchedules = $numPreachers * $numChurches;
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', "Jadwal pertukaran khotbah berhasil dibuat otomatis. Total {$totalSchedules} jadwal dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error generating sermon schedules: ' . $e->getMessage());
            
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }
}