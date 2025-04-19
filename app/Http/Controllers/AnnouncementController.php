<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with(['church', 'user']);
        
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        $announcements = $query->paginate(10);
        return view('pengumuman.index', compact('announcements'));
    }

    public function create()
    {
        $churches = Church::all();
        return view('pengumuman.create', compact('churches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'title' => 'required',
            'announcement_date' => 'required|date',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('announcements', 'public');
            $validated['banner'] = $path;
        }

        Announcement::create($validated);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function edit(Announcement $announcement)
    {
        $churches = Church::all();
        return view('pengumuman.edit', compact('announcement', 'churches')); // Changed from announcements.edit
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'title' => 'required',
            'announcement_date' => 'required|date',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('announcements', 'public');
            $validated['banner'] = $path;
        }

        $announcement->update($validated);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function destroy(Announcement $announcement)
    {
        try {
            if ($announcement->banner) {
                Storage::disk('public')->delete($announcement->banner);
            }
            
            $announcement->delete();
            return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('pengumuman.index')->with('error', 'Gagal menghapus pengumuman');
        }
    }
}
