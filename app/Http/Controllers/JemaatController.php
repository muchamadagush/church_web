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
        
        $query = User::where('role', 'jemaat')
                    ->orderBy('created_at', 'desc');
        
        // Apply search filter if search parameter exists
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('fullname', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        $jemaats = $query->paginate(10);
        
        // Append search parameter to pagination links
        if ($search) {
            $jemaats->appends(['search' => $search]);
        }
        
        return view('jemaat.index', compact('jemaats', 'search'));
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
            'username' => 'required|unique:users',
            'fullname' => 'required',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|min:6',
            'dateofbirth' => 'required',
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
            'username' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'dateofbirth' => 'required|date',
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

    public function export() 
    {
        if (!PermissionHelper::hasPermission('download', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return Excel::download(new JemaatExport, 'data-jemaat.xlsx');
    }

    public function show(User $jemaat)
    {
        return redirect()->route('jemaat.index');
    }
}
