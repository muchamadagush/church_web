<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Church;
use App\Exports\JemaatExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\PermissionHelper;
use DateTime;

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
        
        // Get current authenticated user
        $user = auth()->user();
        $isGembala = $user->role === 'gembala';
        
        // If user is gembala, force churchId to be their church_id
        if ($isGembala) {
            $churchId = $user->church_id;
        } else {
            // If not gembala, get churches for filter dropdown
            $churches = Church::all();
            
            // If no church_id provided and churches exist, use the first one as default
            if (!$churchId && $churches->count() > 0) {
                $churchId = $churches->first()->id;
            }
        }
        
        // Get churches only if not gembala (for dropdown)
        $churches = $isGembala ? null : Church::all();
        
        $query = User::where('role', 'jemaat')
                    ->orderBy('created_at', 'asc');
        
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
                          
            // Calculate age category statistics
            $allJemaat = User::where('role', 'jemaat')
                          ->where('church_id', $churchId)
                          ->get();
                          
            $dewasaCount = 0;
            $pemudaCount = 0;
            $remajaCount = 0;
            $sekolahMingguCount = 0;
            $totalCount = 0;
            
            foreach($allJemaat as $jemaat) {
                if($jemaat->dateofbirth) {
                    $birthDate = new DateTime($jemaat->dateofbirth);
                    $today = new DateTime('today');
                    $age = $birthDate->diff($today)->y;
                    $isParent = ($jemaat->family_status == 'kepala_keluarga' || $jemaat->family_status == 'istri');
                    
                    if($age >= 31 || $isParent) {
                        $dewasaCount++;
                    } elseif($age >= 18) {
                        $pemudaCount++;
                    } elseif($age >= 13) {
                        $remajaCount++;
                    } else {
                        $sekolahMingguCount++;
                    }
                    $totalCount++;
                }
            }
        } else {
            // Initialize with zero if no church is selected
            $dewasaCount = 0;
            $pemudaCount = 0;
            $remajaCount = 0;
            $sekolahMingguCount = 0;
            $totalCount = 0;
        }

        $canEdit = PermissionHelper::hasPermission('edit', 'jemaat');
        $canDelete = PermissionHelper::hasPermission('delete', 'jemaat');

        return view('jemaat.index', compact(
            'jemaats', 
            'search', 
            'churches', 
            'currentChurch', 
            'pastor', 
            'kkCount', 
            'churchId', 
            'canEdit', 
            'canDelete',
            'dewasaCount',
            'pemudaCount',
            'remajaCount',
            'sekolahMingguCount',
            'totalCount',
            'isGembala'
        ));
    }

    public function create()
    {
        if (!PermissionHelper::hasPermission('create', 'jemaat')) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Get current authenticated user
        $user = auth()->user();
        $isGembala = $user->role === 'gembala';
        
        // If user is gembala, we'll use their church_id
        if ($isGembala) {
            $churchId = $user->church_id;
            $selectedChurch = Church::find($churchId);
            
            return view('jemaat.create', compact('isGembala', 'churchId', 'selectedChurch'));
        } else {
            // For admin, show all churches
            $churches = Church::all();
            return view('jemaat.create', compact('churches', 'isGembala'));
        }
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

        // If user is gembala, override the church_id with their church_id
        $user = auth()->user();
        if ($user->role === 'gembala') {
            $validated['church_id'] = $user->church_id;
        }

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

    public function statistics()
    {
        // Get all churches for the table
        $churches = Church::all();
        $churchStats = [];
        
        // For each church, calculate gender and age categories
        foreach($churches as $church) {
            $jemaatData = User::where('role', 'jemaat')
                            ->where('church_id', $church->id)
                            ->get();
            
            // Initialize counters
            $maleCount = 0;
            $femaleCount = 0;
            $maleDewasa = 0;
            $femaleDewasa = 0;
            $malePemuda = 0;
            $femalePemuda = 0;
            $maleRemaja = 0;
            $femaleRemaja = 0;
            $maleSMinggu = 0;
            $femaleSMinggu = 0;
            
            foreach($jemaatData as $jemaat) {
                if($jemaat->dateofbirth) {
                    $birthDate = new DateTime($jemaat->dateofbirth);
                    $today = new DateTime('today');
                    $age = $birthDate->diff($today)->y;
                    $isParent = ($jemaat->family_status == 'kepala_keluarga' || $jemaat->family_status == 'istri');
                    
                    // Count by gender
                    if($jemaat->gender == 'male') {
                        $maleCount++;
                        
                        // Count by age category (male)
                        if($age >= 31 || $isParent) {
                            $maleDewasa++;
                        } elseif($age >= 18) {
                            $malePemuda++;
                        } elseif($age >= 13) {
                            $maleRemaja++;
                        } else {
                            $maleSMinggu++;
                        }
                    } else {
                        $femaleCount++;
                        
                        // Count by age category (female)
                        if($age >= 31 || $isParent) {
                            $femaleDewasa++;
                        } elseif($age >= 18) {
                            $femalePemuda++;
                        } elseif($age >= 13) {
                            $femaleRemaja++;
                        } else {
                            $femaleSMinggu++;
                        }
                    }
                }
            }
            
            // Store statistics for this church
            $churchStats[] = [
                'church' => $church,
                'maleCount' => $maleCount,
                'femaleCount' => $femaleCount,
                'maleDewasa' => $maleDewasa,
                'femaleDewasa' => $femaleDewasa,
                'malePemuda' => $malePemuda,
                'femalePemuda' => $femalePemuda,
                'maleRemaja' => $maleRemaja,
                'femaleRemaja' => $femaleRemaja,
                'maleSMinggu' => $maleSMinggu,
                'femaleSMinggu' => $femaleSMinggu,
                'totalDewasa' => $maleDewasa + $femaleDewasa,
                'totalPemuda' => $malePemuda + $femalePemuda,
                'totalRemaja' => $maleRemaja + $femaleRemaja,
                'totalSMinggu' => $maleSMinggu + $femaleSMinggu,
                'totalMale' => $maleCount,
                'totalFemale' => $femaleCount,
                'total' => $maleCount + $femaleCount
            ];
        }
        
        return view('jemaat.statistics', compact('churchStats'));
    }
}
