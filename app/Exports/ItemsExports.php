<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Item::with('category')->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'No',
            'Nama Item',
            'Kategori',
            'Divisi PJ',
            'Total Stock',
            'Dipinjam',
            'Rusak',
            'Tersedia',
            'Tanggal Dibuat'
        ];
    }

    /**
    * @param mixed $row
    * @return array
    */
    public function map($item): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        return [
            $rowNumber,
            $item->name,
            $item->category->name ?? 'Tidak Ada Kategori',
            $item->category->division_pj ?? '-',
            $item->total,
            $item->lending_total,
            $item->broken,
            $item->available,
            $item->created_at->format('d/m/Y H:i')
        ];
    }

    /**
    * @param Worksheet $sheet
    */
    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style untuk data
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:I' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Warna khusus untuk status available
        for ($row = 2; $row <= $lastRow; $row++) {
            $available = $sheet->getCell('H' . $row)->getValue();
            if ($available > 0) {
                $sheet->getStyle('H' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C6EFCE']
                    ],
                    'font' => [
                        'color' => ['rgb' => '006100']
                    ]
                ]);
            } else {
                $sheet->getStyle('H' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC7CE']
                    ],
                    'font' => [
                        'color' => ['rgb' => '9C0006']
                    ]
                ]);
            }
        }

        // Set tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(20);
        
        // Center align untuk kolom nomor, total, lending, broken, available
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
    * @return array
    */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Nama Item
            'C' => 20,  // Kategori
            'D' => 15,  // Divisi PJ
            'E' => 12,  // Total Stock
            'F' => 12,  // Dipinjam
            'G' => 10,  // Rusak
            'H' => 12,  // Tersedia
            'I' => 18,  // Tanggal Dibuat
        ];
    }
}