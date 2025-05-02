<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JemaatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return User::where('role', 'jemaat')
            ->with('church')
            ->get();
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Username',
            'Nama Lengkap',
            'Tanggal Lahir',
            'Alamat',
            'Gereja'
        ];
    }
    
    public function map($jemaat): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        // More robust error handling for church name
        $churchName = '-';
        if ($jemaat->church_id && $jemaat->church) {
            $churchName = $jemaat->church->name;
        }
        
        return [
            $rowNumber,
            $jemaat->username,
            $jemaat->fullname,
            $jemaat->dateofbirth ? date('d M Y', strtotime($jemaat->dateofbirth)) : '-',
            $jemaat->address,
            $churchName
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}