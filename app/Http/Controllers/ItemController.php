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

    /**
     * Get lending details for a specific item
     */
    public function getLendings(Item $item)
    {
        // Ambil data peminjaman berdasarkan item_id
        $lendings = $item->lendings()
            ->orderBy('lending_date', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'item' => $item,
            'lendings' => $lendings
        ]);
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
        try {
            // Ambil data items dengan relasi category
            $items = Item::with('category')->get();
            
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Items Data');
            
            // ============ TAMBAHKAN JUDUL ============
            // Judul Utama
            $sheet->setCellValue('A1', 'LAPORAN DATA BARANG (ITEMS)');
            $sheet->mergeCells('A1:I1'); // Merge dari kolom A sampai I
            
            // Sub judul / informasi tambahan
            $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A2:I2');
            
            $sheet->setCellValue('A3', 'Total Barang: ' . $items->count() . ' item');
            $sheet->mergeCells('A3:I3');
            
            // Style untuk judul
            $titleStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E75B6']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            
            $sheet->getStyle('A1:I1')->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(25);
            
            // Style untuk sub judul
            $subTitleStyle = [
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => '333333']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            
            $sheet->getStyle('A2:I3')->applyFromArray($subTitleStyle);
            
            // Header columns di baris ke-5
            $headers = [
                'A5' => 'No',
                'B5' => 'Nama Item',
                'C5' => 'Kategori',
                'D5' => 'Divisi PJ',
                'E5' => 'Total Stock',
                'F5' => 'Dipinjam',
                'G5' => 'Rusak',
                'H5' => 'Tersedia',
                'I5' => 'Tanggal Dibuat'
            ];
            
            // Set headers
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style for header (di baris 5)
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
            
            $sheet->getStyle('A5:I5')->applyFromArray($headerStyle);
            
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
            
            // Add data mulai dari baris ke-6
            $row = 6;
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
            if ($lastRow >= 6) {
                $sheet->getStyle('A6:I' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E6:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            
            // Set row height untuk header
            $sheet->getRowDimension(5)->setRowHeight(20);
            
            // Freeze pane agar judul tetap terlihat saat scroll
            $sheet->freezePane('A6');
            
            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="items_export_' . date('Y-m-d_His') . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Expires: 0');
            header('Pragma: public');
            
            // Output to browser
            $writer->save('php://output');
            exit();
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal export Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}