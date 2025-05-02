<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Church;
use App\Exports\JemaatExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JemaatController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'jemaat');

        if ($request->search) {
            $query->where('fullname', 'like', "%{$request->search}%");
        }

        $jemaats = $query->paginate(10);
        
        return view('jemaat.index', compact('jemaats'));
    }

    public function create()
    {
        $churches = Church::all();
        return view('jemaat.create', compact('churches'));
    }

    public function store(Request $request)
    {
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
        $jemaat = User::findOrFail($id);
        $churches = Church::all();
        return view('jemaat.edit', compact('jemaat', 'churches'));
    }

    public function update(Request $request, $id)
    {
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
        $jemaat->delete();
        return redirect()->route('jemaat.index')->with('success', 'Jemaat berhasil dihapus');
    }

    public function export() 
    {
        return Excel::download(new JemaatExport, 'data-jemaat.xlsx');
    }

    public function show(User $jemaat)
    {
        return redirect()->route('jemaat.index');
    }
}
