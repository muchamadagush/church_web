<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Str;
use File;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Announcement::orderBy('created_at', 'desc');
        
        // Apply search filter if search parameter exists
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        
        $announcements = $query->paginate(10);
        
        // Append search parameter to pagination links
        if ($search) {
            $announcements->appends(['search' => $search]);
        }
        
        $canEdit = PermissionHelper::hasPermission('edit', 'pengumuman');
        $canDelete = PermissionHelper::hasPermission('delete', 'pengumuman');

        return view('pengumuman.index', compact('announcements', 'search', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        return view('pengumuman.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'announcement_date' => 'required|date',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Add user_id to validated data
        $validated['user_id'] = auth()->id();

        if ($request->hasFile('banner')) {
            // Create directory if it doesn't exist
            $path = public_path('announcements');
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }
            
            // Generate unique filename
            $file = $request->file('banner');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Move the file to public/announcements
            $file->move($path, $fileName);
            
            // Store the relative path for database
            $validated['banner'] = 'announcements/' . $fileName;
        }

        Announcement::create($validated);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function edit(Announcement $announcement)
    {
        return view('pengumuman.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'announcement_date' => 'required|date',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('banner')) {
            // Delete old file if exists
            if ($announcement->banner && file_exists(public_path($announcement->banner))) {
                unlink(public_path($announcement->banner));
            }
            
            // Create directory if it doesn't exist
            $path = public_path('announcements');
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }
            
            // Generate unique filename
            $file = $request->file('banner');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Move the file to public/announcements
            $file->move($path, $fileName);
            
            // Store the relative path for database
            $validated['banner'] = 'announcements/' . $fileName;
        }

        $announcement->update($validated);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function destroy(Announcement $announcement)
    {
        try {
            if ($announcement->banner && file_exists(public_path($announcement->banner))) {
                unlink(public_path($announcement->banner));
            }
            
            $announcement->delete();
            return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('pengumuman.index')->with('error', 'Gagal menghapus pengumuman');
        }
    }
}
