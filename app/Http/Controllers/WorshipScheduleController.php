<?php

namespace App\Http\Controllers;

use App\Models\WorshipSchedule;
use App\Models\Church;
use App\Models\User;
use Illuminate\Http\Request;

class WorshipScheduleController extends Controller
{
    public function index()
    {
        return view('worship-schedules.index');
    }

    public function show()
    {
        $schedules = WorshipSchedule::with(['user', 'church'])->paginate(10);

        $canEdit = PermissionHelper::hasPermission('edit', 'worship-schedules');
        $canDelete = PermissionHelper::hasPermission('delete', 'worship-schedules');

        return view('worship-schedules.sermons.index', compact('schedules', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $users = User::where('role', 'gembala')->get();
        $churches = Church::all();
        return view('worship-schedules.sermons.form', compact('users', 'churches'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'church_id' => 'required|exists:churches,id',
                'title' => 'nullable|string',
                'description' => 'nullable|string'
            ]);

            WorshipSchedule::create($validated);
            return redirect()->route('worship-schedules.sermons.index')
                           ->with('success', 'Jadwal pertukaran khotbah berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan jadwal');
        }
    }

    public function edit(WorshipSchedule $schedule)
    {
        $users = User::where('role', 'gembala')->get();
        $churches = Church::all();
        return view('worship-schedules.sermons.form', compact('schedule', 'users', 'churches'));
    }

    public function update(Request $request, WorshipSchedule $schedule)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'church_id' => 'required|exists:churches,id',
                'title' => 'nullable|string',
                'description' => 'nullable|string'
            ]);

            $schedule->update($validated);
            return redirect()->route('worship-schedules.sermons.index')
                           ->with('success', 'Jadwal pertukaran khotbah berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui jadwal');
        }
    }

    public function destroy(WorshipSchedule $schedule)
    {
        try {
            $schedule->delete();
            return redirect()->route('worship-schedules.sermons.index')
                           ->with('success', 'Jadwal pertukaran khotbah berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus jadwal');
        }
    }
}
