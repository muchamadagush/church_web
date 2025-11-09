<?php

namespace App\Http\Controllers;

use App\Models\PrayerSchedule;
use Illuminate\Http\Request;
use App\Models\Church;
use App\Helpers\PermissionHelper;

class PrayerScheduleController extends Controller
{
    public function index()
    {
        $schedules = PrayerSchedule::orderBy('start_datetime')->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');

        return view('worship-schedules.prayer-schedules.index', compact('schedules', 'canEdit', 'canDelete'));
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
}