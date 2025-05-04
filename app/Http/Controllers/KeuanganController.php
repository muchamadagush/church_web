<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Exports\KeuanganExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KeuanganController extends Controller
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
        
        $query = Keuangan::orderBy('tanggal', 'desc');
        
        // Apply search filter if search parameter exists
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', '%' . $search . '%')
                  ->orWhere('tanggal', 'like', '%' . $search . '%');
            });
        }
        
        $keuangan = $query->paginate(10);
        
        // For summary calculations, we still need all records
        $allKeuangan = Keuangan::all();
        $totalDebit = $allKeuangan->sum('debit');
        $totalKredit = $allKeuangan->sum('kredit');
        $total = $totalDebit - $totalKredit;
        
        // Append search parameter to pagination links
        if ($search) {
            $keuangan->appends(['search' => $search]);
        }
        
        return view('keuangan.index', compact('keuangan', 'total', 'totalDebit', 'totalKredit', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('keuangan.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'jenis' => 'required|in:debit,kredit',
            'keterangan' => 'required|string|max:255',
        ]);

        $keuangan = new Keuangan();
        $keuangan->tanggal = $request->tanggal;
        $keuangan->keterangan = $request->keterangan;
        
        // Set debit or kredit based on the selected type
        if ($request->jenis === 'debit') {
            $keuangan->debit = $request->nominal;
            $keuangan->kredit = null;
        } else {
            $keuangan->kredit = $request->nominal;
            $keuangan->debit = null;
        }
        
        $keuangan->save();

        return redirect()->route('keuangan.index')->with('success', 'Data keuangan berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $keuangan = Keuangan::findOrFail($id);
        return view('keuangan.form', compact('keuangan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'jenis' => 'required|in:debit,kredit',
            'keterangan' => 'required|string|max:255',
        ]);

        $keuangan = Keuangan::findOrFail($id);
        $keuangan->tanggal = $request->tanggal;
        $keuangan->keterangan = $request->keterangan;
        
        // Set debit or kredit based on the selected type
        if ($request->jenis === 'debit') {
            $keuangan->debit = $request->nominal;
            $keuangan->kredit = null;
        } else {
            $keuangan->kredit = $request->nominal;
            $keuangan->debit = null;
        }
        
        $keuangan->save();

        return redirect()->route('keuangan.index')->with('success', 'Data keuangan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $keuangan = Keuangan::findOrFail($id);
        $keuangan->delete();

        return redirect()->route('keuangan.index')->with('success', 'Data keuangan berhasil dihapus');
    }
    
    /**
     * Download financial data as Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        return Excel::download(new KeuanganExport, 'data_keuangan.xlsx');
    }
}