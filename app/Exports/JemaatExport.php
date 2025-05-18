<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use DateTime;

class JemaatExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithCustomStartCell, WithEvents
{
    protected $search;
    protected $churchId;
    protected $isGembala;
    protected $churchInfo;
    protected $pastorInfo;
    protected $kkCount;

    public function __construct($search = null, $churchId = null, $isGembala = false, $churchInfo = null, $pastorInfo = null, $kkCount = 0)
    {
        $this->search = $search;
        $this->churchId = $churchId;
        $this->isGembala = $isGembala;
        $this->churchInfo = $churchInfo;
        $this->pastorInfo = $pastorInfo;
        $this->kkCount = $kkCount;
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
    
    public function title(): string
    {
        return 'Data Jemaat';
    }
    
    public function startCell(): string
    {
        return 'A6'; // Start actual data from row 6 to give space for the header info
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
        
        // Removed 'Gereja' column from headings
        
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
        
        // Removed church name from data array
        
        return $data;
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
        ];
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Add church info header
                $churchName = $this->churchInfo ? $this->churchInfo->name : 'Tidak tersedia';
                $pastorName = $this->pastorInfo ? $this->pastorInfo->fullname : 'Tidak tersedia';
                $kkCount = $this->kkCount;
                
                $sheet = $event->sheet->getDelegate();
                
                $sheet->setCellValue('A1', 'DATA JEMAAT');
                $sheet->mergeCells('A1:G1');
                
                $sheet->setCellValue('A2', 'Nama Gereja:');
                $sheet->setCellValue('C2', $churchName);
                
                $sheet->setCellValue('A3', 'Nama Gembala');
                $sheet->setCellValue('C3', $pastorName);
                
                $sheet->setCellValue('A4', 'Jumlah KK');
                $sheet->setCellValue('C4', $kkCount);
                
                // Set alignment
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(16);
                
                // Add some styling to header
                $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4A44D');
                $sheet->getStyle('A1:G1')->getFont()->getColor()->setRGB('FFFFFF');
                
                // For the church info rows
                $sheet->getStyle('A2:A4')->getFont()->setBold(true);
                $sheet->getStyle('C2:C4')->getFont()->setBold(false);
            }
        ];
    }
}