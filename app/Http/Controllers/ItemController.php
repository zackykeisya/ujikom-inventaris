<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ItemController extends Controller
{

    public function index()
    {
        $items = Item::with('category')->get();
        $categories = Category::all();
        
        return view('admin.items', compact('items', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'total' => 'required|integer|min:1',
        ]);

        $item = Item::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'total' => $request->total,
            'broken' => 0,
            'lending_total' => 0,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Item berhasil ditambahkan']);
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'total' => 'required|integer|min:1',
            'new_broken' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'category_id' => $request->category_id,
            'total' => $request->total,
        ];
        
        if ($request->filled('new_broken') && $request->new_broken > 0) {
            $data['broken'] = $item->broken + $request->new_broken;
        }

        $item->update($data);
        
        return response()->json(['success' => true, 'message' => 'Item berhasil diupdate']);
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Item berhasil dihapus']);
    }

    public function export()
    {
        // Ambil data items dengan relasi category
        $items = Item::with('category')->get();
        
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setTitle('Items Data');
        
        // Header columns
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Item',
            'C1' => 'Kategori',
            'D1' => 'Divisi PJ',
            'E1' => 'Total Stock',
            'F1' => 'Dipinjam',
            'G1' => 'Rusak',
            'H1' => 'Tersedia',
            'I1' => 'Tanggal Dibuat'
        ];
        
        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style for header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
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
        ];
        
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(18);
        
        // Add data
        $row = 2;
        $no = 1;
        
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item->name);
            $sheet->setCellValue('C' . $row, $item->category->name ?? 'Tidak Ada Kategori');
            $sheet->setCellValue('D' . $row, $item->category->division_pj ?? '-');
            $sheet->setCellValue('E' . $row, $item->total);
            $sheet->setCellValue('F' . $row, $item->lending_total);
            $sheet->setCellValue('G' . $row, $item->broken);
            $sheet->setCellValue('H' . $row, $item->available);
            $sheet->setCellValue('I' . $row, $item->created_at->format('d/m/Y H:i'));
            
            // Color based on available stock
            if ($item->available > 0) {
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
            
            $row++;
            $no++;
        }
        
        // Apply borders to all data cells
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
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
            
            // Center alignment for numeric columns
            $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E2:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(20);
        
        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="items_' . date('Y-m-d_His') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Output to browser
        $writer->save('php://output');
        exit();
    }
}