<?php

namespace App\Exports;

use App\Models\Keuangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KeuanganExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Keuangan::orderBy('tanggal', 'desc')->get();
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Debit (Pemasukan)',
            'Kredit (Pengeluaran)',
            'Keterangan'
        ];
    }
    
    public function map($keuangan): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        return [
            $rowNumber,
            $keuangan->tanggal ? date('d M Y', strtotime($keuangan->tanggal)) : '-',
            $keuangan->debit ? 'Rp. ' . number_format($keuangan->debit, 0, ',', '.') : '-',
            $keuangan->kredit ? 'Rp. ' . number_format($keuangan->kredit, 0, ',', '.') : '-',
            $keuangan->keterangan ?? '-'
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}