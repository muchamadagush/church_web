<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\SermonSchedule;
use App\Models\SermonScheduleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\PermissionHelper;

class SermonScheduleController extends Controller
{
    public function index()
    {
        $schedules = SermonSchedule::with(['churches', 'details'])->get();

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');
        
        return view('worship-schedules.sermons.index', compact('schedules', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $churches = Church::all();
        
        // Get all months that are already assigned to schedules, excluding the current one if editing
        $usedMonths = SermonScheduleDetail::query()
            ->when(request()->route('sermon'), function($query, $sermon) {
                return $query->where('sermon_schedule_id', '!=', $sermon);
            })
            ->pluck('month')
            ->toArray();
        
        return view('worship-schedules.sermons.form', compact('churches', 'usedMonths'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pengkhotbah' => 'required|string|max:255',
            'churches' => 'required|array',
            'churches.*.church_id' => 'required|exists:churches,id',
            'churches.*.month' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if this month is already used in another record
                    $exists = SermonScheduleDetail::where('month', $value)
                        ->when(request()->route('sermon'), function($query, $sermon) {
                            return $query->where('sermon_schedule_id', '!=', $sermon);
                        })
                        ->exists();
                    
                    if ($exists) {
                        $fail("Bulan $value sudah digunakan di jadwal lain.");
                    }
                }
            ],
        ]);

        try {
            DB::beginTransaction();
            
            $schedule = SermonSchedule::create([
                'pengkhotbah' => $request->pengkhotbah
            ]);

            foreach ($request->churches as $church) {
                $schedule->details()->create([
                    'church_id' => $church['church_id'],
                    'month' => $church['month']
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
        
        // Get used months excluding those from this schedule
        $usedMonths = SermonScheduleDetail::where('sermon_schedule_id', '!=', $schedule->id)
            ->pluck('month')
            ->toArray();
        
        return view('worship-schedules.sermons.form', [
            'schedule' => $schedule,
            'churches' => $churches,
            'usedMonths' => $usedMonths
        ]);
    }

    public function update(Request $request, SermonSchedule $schedule)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string',
            'churches' => 'required|array',
            'churches.*.church_id' => 'required|exists:churches,id',
            'churches.*.month' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($schedule) {
                    // Check if this month is already used in another record
                    $exists = SermonScheduleDetail::where('month', $value)
                        ->where('sermon_schedule_id', '!=', $schedule->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail("Bulan $value sudah digunakan di jadwal lain.");
                    }
                }
            ]
        ]);

        try {
            DB::beginTransaction();
            
            // Update pengkhotbah
            $schedule->update([
                'pengkhotbah' => $request->pengkhotbah
            ]);

            // Delete existing details
            $schedule->details()->delete();

            // Create new details
            foreach ($request->churches as $church) {
                $schedule->details()->create([
                    'church_id' => $church['church_id'],
                    'month' => $church['month']
                ]);
            }

            DB::commit();
            return redirect()
                ->route('worship-schedules.sermons.index')
                ->with('success', 'Jadwal khotbah berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
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
}