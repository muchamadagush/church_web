<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Church;
use App\Exports\JemaatExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\PermissionHelper;

class JemaatController extends Controller
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
        $churchId = $request->input('church_id');
        
        // Get churches for filter dropdown
        $churches = Church::all();
        
        // If no church_id provided and churches exist, use the first one as default
        if (!$churchId && $churches->count() > 0) {
            $churchId = $churches->first()->id;
        }
        
        $query = User::where('role', 'jemaat')
                    ->orderBy('created_at', 'desc');
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('fullname', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Apply church filter (now always applied since we have a default)
        if ($churchId) {
            $query->where('church_id', $churchId);
        }
        
        $jemaats = $query->paginate(10);
        
        // Append parameters to pagination links
        if ($search || $churchId) {
            $jemaats->appends([
                'search' => $search,
                'church_id' => $churchId
            ]);
        }
        
        // Get current church info for note section
        $currentChurch = null;
        $pastor = null;
        $kkCount = 0;
        
        if ($churchId) {
            $currentChurch = Church::find($churchId);
            
            // Find pastor (user with role 'gembala') for this church
            $pastor = User::where('role', 'gembala')
                         ->where('church_id', $churchId)
                         ->first();
                         
            // Count household heads (KK)
            $kkCount = User::where('role', 'jemaat')
                          ->where('church_id', $churchId)
                          ->where('family_status', 'kepala_keluarga')
                          ->count();
        }

        $canEdit = PermissionHelper::hasPermission('edit', 'jemaat');
        $canDelete = PermissionHelper::hasPermission('delete', 'jemaat');

        return view('jemaat.index', compact('jemaats', 'search', 'churches', 'currentChurch', 'pastor', 'kkCount', 'churchId', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        if (!PermissionHelper::hasPermission('create', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $churches = Church::all();
        return view('jemaat.create', compact('churches'));
    }

    public function store(Request $request)
    {
        if (!PermissionHelper::hasPermission('create', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $validated = $request->validate([
            'username' => 'nullable|unique:users',
            'fullname' => 'required',
            'email' => 'nullable|email|unique:users',
            'password' => 'nullable|min:6',
            'dateofbirth' => 'required',
            'birthplace' => 'required',
            'gender' => 'required|in:male,female',
            'family_status' => 'required',
            'address' => 'required',
            'church_id' => 'required|exists:churches,id'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'jemaat';

        User::create($validated);

        return redirect()->route('jemaat.index')->with('success', 'Jemaat berhasil ditambahkan');
    }

    public function edit($id)
    {
        if (!PermissionHelper::hasPermission('edit', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $jemaat = User::findOrFail($id);
        $churches = Church::all();
        return view('jemaat.edit', compact('jemaat', 'churches'));
    }

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::hasPermission('edit', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $request->validate([
            'username' => 'nullable|string|max:255', // Changed from required to nullable
            'fullname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'dateofbirth' => 'required|date',
            'birthplace' => 'required|string',
            'gender' => 'required|in:male,female',
            'family_status' => 'required|string',
            'address' => 'required|string',
            'church_id' => 'required|exists:churches,id',
        ]);

        $jemaat = User::findOrFail($id);
        $jemaat->update($request->all());

        return redirect()->route('jemaat.index')->with('success', 'Jemaat berhasil diubah.');
    }

    public function destroy(User $jemaat)
    {
        if (!PermissionHelper::hasPermission('delete', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $jemaat->delete();
        return redirect()->route('jemaat.index')->with('success', 'Jemaat berhasil dihapus');
    }

    public function export(Request $request) 
    {
        if (!PermissionHelper::hasPermission('download', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $search = $request->input('search');
        $churchId = $request->input('church_id');
        
        // If no church_id provided and churches exist, use the first one as default
        if (!$churchId) {
            $firstChurch = Church::first();
            if ($firstChurch) {
                $churchId = $firstChurch->id;
            }
        }
        
        // Get church, pastor, and KK count data for the export
        $churchInfo = null;
        $pastorInfo = null;
        $kkCount = 0;
        
        if ($churchId) {
            $churchInfo = Church::find($churchId);
            
            // Find pastor (user with role 'gembala') for this church
            $pastorInfo = User::where('role', 'gembala')
                         ->where('church_id', $churchId)
                         ->first();
                         
            // Count household heads (KK)
            $kkCount = User::where('role', 'jemaat')
                          ->where('church_id', $churchId)
                          ->where('family_status', 'kepala_keluarga')
                          ->count();
        }
        
        // Check if the current user is a gembala
        $isGembala = auth()->user()->role === 'gembala';

        return Excel::download(new JemaatExport($search, $churchId, $isGembala, $churchInfo, $pastorInfo, $kkCount), 'data-jemaat.xlsx');
    }

    public function show(User $jemaat)
    {
        return redirect()->route('jemaat.index');
    }
}
