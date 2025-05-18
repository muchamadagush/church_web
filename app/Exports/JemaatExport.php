<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DateTime;

class JemaatExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    protected $churchId;
    protected $isGembala;

    public function __construct($search = null, $churchId = null, $isGembala = false)
    {
        $this->search = $search;
        $this->churchId = $churchId;
        $this->isGembala = $isGembala;
    }
    
    public function query()
    {
        $query = User::query()
            ->where('role', 'jemaat')
            ->with('church')
            ->orderBy('created_at', 'desc');
        
        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhere('fullname', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply church filter
        if ($this->churchId) {
            $query->where('church_id', $this->churchId);
        }
        
        return $query;
    }
    
    public function headings(): array
    {
        $headings = [
            'No',
            'Status',
            'Nama Lengkap',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
        ];
        
        // Only include the Usia column if the user is not a gembala
        if (!$this->isGembala) {
            $headings[] = 'Usia';
        }
        
        $headings[] = 'Gereja';
        
        return $headings;
    }
    
    public function map($jemaat): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        // Format family status
        $status = $jemaat->family_status;
        if ($status == 'kepala_keluarga') {
            $status = 'Ayah';
        } elseif ($status == 'istri') {
            $status = 'Istri';
        } elseif ($status == 'anak') {
            $status = 'Anak';
        }
        
        // Calculate age and category, but only if not a gembala
        $age = '';
        if (!$this->isGembala && $jemaat->dateofbirth) {
            $birthDate = new DateTime($jemaat->dateofbirth);
            $today = new DateTime('today');
            $ageYears = $birthDate->diff($today)->y;
            $isParent = ($jemaat->family_status == 'kepala_keluarga' || $jemaat->family_status == 'istri');
            
            if ($ageYears >= 31 || $isParent) {
                $category = "Dewasa";
            } elseif ($ageYears >= 18) {
                $category = "Pemuda";
            } elseif ($ageYears >= 13) {
                $category = "Remaja";
            } else {
                $category = "Sekolah Minggu";
            }
            
            $age = $ageYears . " (" . $category . ")";
        }
        
        // More robust error handling for church name
        $churchName = '-';
        if ($jemaat->church) {
            $churchName = $jemaat->church->name;
        }
        
        $data = [
            $rowNumber,
            $status,
            $jemaat->fullname,
            $jemaat->birthplace ?? '-',
            $jemaat->dateofbirth ? date('d M Y', strtotime($jemaat->dateofbirth)) : '-',
            $jemaat->gender == 'male' ? 'Laki Laki' : 'Perempuan',
        ];
        
        // Only add age if not a gembala
        if (!$this->isGembala) {
            $data[] = $age;
        }
        
        $data[] = $churchName;
        
        return $data;
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}