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
        $schedules = PrayerSchedule::orderBy('tanggal')->get();

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
            'tanggal' => 'required|date',
            'nama_gereja' => 'required|string',
            'pimpinan_pujian' => 'required|string',
            'pengkhotbah' => 'required|string',
        ]);

        PrayerSchedule::create($validated);
        return redirect()->route('worship-schedules.prayer-schedules.index')->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(PrayerSchedule $prayer_schedule)
    {
        $churches = Church::all();
        return view('worship-schedules.prayer-schedules.form', compact('prayer_schedule', 'churches'));
    }

    public function update(Request $request, PrayerSchedule $prayer_schedule)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_gereja' => 'required|string',
            'pimpinan_pujian' => 'required|string',
            'pengkhotbah' => 'required|string',
        ]);

        $prayer_schedule->update($validated);
        return redirect()->route('worship-schedules.prayer-schedules.index')->with('success', 'Jadwal berhasil diperbarui');
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