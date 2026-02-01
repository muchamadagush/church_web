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

    /**
     * Calculate all Sundays in a given month
     */
    private function getAllSundaysInMonth($monthName, $year)
    {
        $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $monthNumber = array_search($monthName, $monthNames) + 1;
        
        $date = Carbon::create($year, $monthNumber, 1);
        $lastDayOfMonth = $date->endOfMonth();
        
        $sundays = [];
        $current = $date->copy();
        
        // Find first Sunday
        while ($current->dayOfWeek !== Carbon::SUNDAY) {
            $current->addDay();
        }
        
        // Collect all Sundays
        while ($current->lte($lastDayOfMonth)) {
            $sundays[] = $current->copy();
            $current->addWeek();
        }
        
        return $sundays;
    }

    /**
     * Generate time slots for a given date
     * Returns array of ['start' => Carbon, 'end' => Carbon]
     */
    private function generateTimeSlots($date)
    {
        return [
            [
                'start' => $date->copy()->setTime(8, 0, 0),
                'end' => $date->copy()->setTime(10, 0, 0),
            ],
            [
                'start' => $date->copy()->setTime(10, 30, 0),
                'end' => $date->copy()->setTime(12, 30, 0),
            ],
            [
                'start' => $date->copy()->setTime(14, 0, 0),
                'end' => $date->copy()->setTime(16, 0, 0),
            ],
            [
                'start' => $date->copy()->setTime(16, 30, 0),
                'end' => $date->copy()->setTime(18, 30, 0),
            ],
        ];
    }

    /**
     * Check if a time slot conflicts with existing bookings
     */
    private function hasTimeConflict($churchId, $startTime, $endTime, $bookings)
    {
        if (!isset($bookings[$churchId])) {
            return false;
        }
        
        foreach ($bookings[$churchId] as $booking) {
            // Check if time ranges overlap
            if ($startTime->lt($booking['end']) && $endTime->gt($booking['start'])) {
                return true;
            }
        }
        
        return false;
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

        \Log::info("=== STARTING SERMON SCHEDULE GENERATION ===");
        \Log::info("Year: {$year}");

        try {
            // Check if schedules exist for this year
            $existingCount = SermonSchedule::whereYear('start_datetime', $year)->count();
            \Log::info("Existing schedules for year {$year}: {$existingCount}");
            
            if ($existingCount > 0) {
                \Log::warning("Cannot generate: schedules already exist for year {$year}");
                return redirect()
                    ->route('worship-schedules.sermons.index')
                    ->with('error', "Jadwal untuk tahun {$year} sudah ada. Hapus jadwal tahun tersebut terlebih dahulu untuk membuat jadwal baru.");
            }

            $preachers = [
                ['name' => 'Pdt. DANIEL JOHNI, S.Th', 'home_church' => 'GGP Bukit Zaitun Kole'],
                ['name' => 'Pdt. ANDARIAS LAYUK LANGI\', S.Th', 'home_church' => 'GGP Salurea'],
                ['name' => 'Pdp. SAHRA PAMO', 'home_church' => 'GGP Pa`Kappan'],
                ['name' => 'Pdm. ANDARIAS MINGGU', 'home_church' => 'GGP Getsemani Bu`Buk'],
                ['name' => 'Pdm. MESAKH BENNU, S.Th', 'home_church' => 'GGP Lembah Pujian To`Lemo'],
                ['name' => 'Pdt. ORVA, S.Pd', 'home_church' => 'GGP Shalom Ne`Me`Se'],
                ['name' => 'Pdm. MATIUS LEPPANG', 'home_church' => 'GGP Solagratia Tiroan'],
                ['name' => 'Pdp. YUNI DATU MALING', 'home_church' => 'GGP El-Shadday Ratte'],
                ['name' => 'Pdp. RINA TAPPI\'', 'home_church' => 'GGP Imanuel Ratte'],
                ['name' => 'Pdm. THOMAS TAPPI', 'home_church' => 'GGP Benteng Batu'],
                ['name' => 'Pdt. SEMUEL SONI, S.Pd', 'home_church' => 'GGP Anugrah Salu Baruppu`'],
            ];

            // Only January to October (10 months)
            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober'];
            $churches = Church::all();
            $numPreachers = count($preachers);
            $numChurches = $churches->count();

            \Log::info("Total preachers: {$numPreachers}");
            \Log::info("Total churches: {$numChurches}");
            \Log::info("Churches list: " . $churches->pluck('name')->implode(', '));

            // Validation: Ensure we have enough churches
            if ($numChurches < 2) {
                \Log::error("Not enough churches: {$numChurches}");
                return redirect()
                    ->route('worship-schedules.sermons.index')
                    ->with('error', 'Minimal 2 gereja diperlukan untuk rotasi pengkhotbah.');
            }

            \Log::info("Starting transaction...");
            \Log::info("Starting transaction...");
            DB::beginTransaction();

            $totalSchedules = 0;
            $schedulesPerPreacher = [];
            
            // Track which churches each preacher has been assigned to in CURRENT MONTH only
            // (Allow repeat churches across different months)
            // Format: ['month']['preacher_name']['church_id'] = true
            $preacherChurchByMonth = [];
            
            // For each month (Jan-Oct), assign each preacher to 1 different church
            foreach ($months as $monthIndex => $month) {
                \Log::info("--- Processing month: {$month} ---");
                
                // Calculate last Sunday of the month
                $datetime = $this->calculateLastSunday($month, $year);
                \Log::info("Last Sunday of {$month}: {$datetime['start']->format('Y-m-d H:i:s')}");
                
                // Initialize month tracking
                $preacherChurchByMonth[$month] = [];
                
                // Try to assign preachers with multiple attempts for random variation
                $maxAttempts = 20;
                $success = false;
                
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    if ($attempt > 1) {
                        \Log::info("Retry attempt {$attempt} for month {$month}");
                    }
                    
                    // Track church bookings for this attempt
                    $churchBookingsThisMonth = [];
                    $monthAssignments = [];
                    
                    // Shuffle preachers for randomization
                    $shuffledPreachers = collect($preachers)->shuffle();
                    
                    $allAssigned = true;
                    
                    // Try to assign each preacher
                    foreach ($shuffledPreachers as $preacher) {
                        // Get available churches
                        $availableChurches = $churches->filter(function($church) use ($preacher, $churchBookingsThisMonth) {
                            if ($church->name === $preacher['home_church']) {
                                return false;
                            }
                            if (isset($churchBookingsThisMonth[$church->id])) {
                                return false;
                            }
                            return true;
                        });
                        
                        if ($availableChurches->isEmpty()) {
                            $allAssigned = false;
                            break;
                        }
                        
                        $selectedChurch = $availableChurches->random();
                        
                        $monthAssignments[] = [
                            'preacher' => $preacher,
                            'church' => $selectedChurch,
                        ];
                        
                        $churchBookingsThisMonth[$selectedChurch->id] = true;
                    }
                    
                    if ($allAssigned) {
                        \Log::info("Successfully matched all preachers for {$month} on attempt {$attempt}");
                        
                        // Create schedules
                        foreach ($monthAssignments as $assignment) {
                            $preacher = $assignment['preacher'];
                            $selectedChurch = $assignment['church'];
                            
                            if (!isset($schedulesPerPreacher[$preacher['name']])) {
                                $schedulesPerPreacher[$preacher['name']] = 0;
                            }
                            
                            $schedule = SermonSchedule::create([
                                'pengkhotbah' => $preacher['name'],
                                'church_id' => $selectedChurch->id,
                                'month' => $month,
                                'start_datetime' => $datetime['start'],
                                'end_datetime' => $datetime['end'],
                            ]);
                            
                            \Log::info("Created: {$preacher['name']} -> {$selectedChurch->name} (ID: {$schedule->id})");
                            
                            $totalSchedules++;
                            $schedulesPerPreacher[$preacher['name']]++;
                        }
                        
                        $success = true;
                        break;
                    }
                }
                
                if (!$success) {
                    \Log::error("FAILED to create valid assignment for {$month} after {$maxAttempts} attempts");
                    DB::rollBack();
                    return redirect()
                        ->route('worship-schedules.sermons.index')
                        ->with('error', "Gagal membuat jadwal untuk bulan {$month} setelah {$maxAttempts} percobaan. Kombinasi gereja dan pengkhotbah tidak memungkinkan.");
                }
                
                \Log::info("Month {$month} completed. Total: {$totalSchedules}");
            }

            \Log::info("All months processed. Validating schedule counts...");
            
            // Validate that each preacher got exactly 10 schedules
            foreach ($preachers as $preacher) {
                $count = $schedulesPerPreacher[$preacher['name']] ?? 0;
                \Log::info("{$preacher['name']}: {$count} schedules");
                
                if ($count !== 10) {
                    \Log::error("VALIDATION FAILED: {$preacher['name']} got {$count} schedules instead of 10");
                    DB::rollBack();
                    return redirect()
                        ->route('worship-schedules.sermons.index')
                        ->with('error', "Gagal membuat jadwal: {$preacher['name']} hanya mendapat {$count} jadwal (harus 10). Tidak cukup gereja untuk rotasi.");
                }
            }

            \Log::info("Validation passed. Committing transaction...");
            DB::commit();
            \Log::info("Transaction committed successfully!");
            \Log::info("=== GENERATION COMPLETED ===");
            \Log::info("Total schedules created: {$totalSchedules}");

            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', "Jadwal pertukaran khotbah untuk tahun {$year} berhasil dibuat. Total {$totalSchedules} jadwal (masing-masing pengkhotbah mendapat 10 jadwal per tahun, 1 jadwal per bulan dari Januari-Oktober di gereja berbeda).");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== GENERATION FAILED ===');
            \Log::error('Exception: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }
}