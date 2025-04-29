<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\SermonSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SermonScheduleController extends Controller
{
    public function index()
    {
        $schedules = SermonSchedule::with(['churches', 'details'])->get();
        return view('worship-schedules.sermons.index', compact('schedules'));
    }

    public function create()
    {
        $churches = Church::all();
        return view('worship-schedules.sermons.form', compact('churches'));
    }

    public function store(Request $request)
    {
        // Debugging: Cek data yang diterima
        \Log::info('Received data:', $request->all());

        $validated = $request->validate([
            'pengkhotbah' => 'required|string',
            'churches' => 'required|array',
            'churches.*.church_id' => 'required|exists:churches,id',
            'churches.*.month' => 'required|string'
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
        $schedule->load('details.church');
        return view('worship-schedules.sermons.form', compact('schedule', 'churches'));
    }

    public function update(Request $request, SermonSchedule $schedule)
    {
        $validated = $request->validate([
            'pengkhotbah' => 'required|string',
            'churches' => 'required|array',
            'churches.*.church_id' => 'required|exists:churches,id',
            'churches.*.month' => 'required|string'
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