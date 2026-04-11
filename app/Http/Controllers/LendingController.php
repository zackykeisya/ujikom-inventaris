<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Lending;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LendingController extends Controller
{
    public function index()
    {
        $lendings = Lending::with('item')->latest()->get();
        $items = Item::where('available', '>', 0)->get(); // Hanya item yang available > 0
        
        return view('staff.lendings', compact('lendings', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.total' => 'required|integer|min:1',
            'borrower_name' => 'required|string|max:255',
            'lending_date' => 'required|date',
        ]);

        // Validasi stock untuk setiap item
        foreach ($request->items as $itemData) {
            $item = Item::find($itemData['item_id']);
            
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item tidak ditemukan'
                ], 422);
            }
            
            if ($itemData['total'] > $item->available) {
                return response()->json([
                    'success' => false,
                    'message' => "Item {$item->name} hanya tersedia {$item->available} buah"
                ], 422);
            }
        }

        // Simpan peminjaman
        foreach ($request->items as $itemData) {
            $lending = Lending::create([
                'item_id' => $itemData['item_id'],
                'borrower_name' => $request->borrower_name,
                'total' => $itemData['total'],
                'lending_date' => $request->lending_date,
            ]);

            // Update lending_total pada item
            $item = Item::find($itemData['item_id']);
            $item->increment('lending_total', $itemData['total']);
        }

        return response()->json(['success' => true, 'message' => 'Peminjaman berhasil ditambahkan']);
    }

    public function returnItem(Lending $lending)
    {
        if ($lending->return_date) {
            return response()->json(['success' => false, 'message' => 'Item sudah dikembalikan'], 422);
        }

        $lending->update(['return_date' => now()]);
        
        $item = $lending->item;
        $item->decrement('lending_total', $lending->total);

        return response()->json(['success' => true, 'message' => 'Item berhasil dikembalikan']);
    }

    public function destroy(Lending $lending)
    {
        try {
            // Jika belum dikembalikan, kurangi lending_total terlebih dahulu
            if (!$lending->return_date) {
                $item = $lending->item;
                $item->decrement('lending_total', $lending->total);
            }
            
            $lending->delete();
            return response()->json(['success' => true, 'message' => 'Peminjaman berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus peminjaman: ' . $e->getMessage()], 500);
        }
    }

    public function export()
    {
        try {
            // Ambil data lendings dengan relasi item
            $lendings = Lending::with('item')->latest()->get();
            
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Lendings Data');
            
            // ============ TAMBAHKAN JUDUL ============
            // Judul Utama
            $sheet->setCellValue('A1', 'LAPORAN DATA PEMINJAMAN BARANG');
            $sheet->mergeCells('A1:I1'); // Merge dari kolom A sampai I
            
            // Sub judul / informasi tambahan
            $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A2:I2');
            
            $sheet->setCellValue('A3', 'Total Peminjaman: ' . $lendings->count() . ' transaksi');
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
            
            // Set headers di baris ke-5 (setelah judul dan sub judul)
            $sheet->setCellValue('A5', 'No');
            $sheet->setCellValue('B5', 'Borrower Name');
            $sheet->setCellValue('C5', 'Item Name');
            $sheet->setCellValue('D5', 'Category');
            $sheet->setCellValue('E5', 'Total Borrowed');
            $sheet->setCellValue('F5', 'Lending Date');
            $sheet->setCellValue('G5', 'Return Date');
            $sheet->setCellValue('H5', 'Status');
            $sheet->setCellValue('I5', 'Days Borrowed');
            
            // Style header (di baris 5)
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
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(12);
            $sheet->getColumnDimension('I')->setWidth(15);
            
            // Add data mulai dari baris ke-6
            $row = 6;
            $no = 1;
            
            foreach ($lendings as $lending) {
                // Hitung hari peminjaman
                $lendingDate = $lending->lending_date;
                $returnDate = $lending->return_date ?? now();
                $daysBorrowed = $lendingDate->diffInDays($returnDate);
                
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $lending->borrower_name);
                $sheet->setCellValue('C' . $row, $lending->item->name ?? 'Item Tidak Ditemukan');
                $sheet->setCellValue('D' . $row, $lending->item->category->name ?? '-');
                $sheet->setCellValue('E' . $row, $lending->total);
                // Format datetime dengan jam dan menit
                $sheet->setCellValue('F' . $row, $lending->lending_date ? $lending->lending_date->format('d/m/Y H:i:s') : '-');
                $sheet->setCellValue('G' . $row, $lending->return_date ? $lending->return_date->format('d/m/Y H:i:s') : '-');
                $sheet->setCellValue('H' . $row, $lending->return_date ? 'Returned' : 'Borrowed');
                $sheet->setCellValue('I' . $row, $lending->return_date ? $daysBorrowed . ' hari' : $daysBorrowed . ' hari (belum kembali)');
                
                // Color for status column
                if ($lending->return_date) {
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
                            'startColor' => ['rgb' => 'FFEB9C']
                        ],
                        'font' => [
                            'color' => ['rgb' => '9C5700']
                        ]
                    ]);
                }
                
                $row++;
                $no++;
            }
            
            // Apply borders to data cells
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
                
                // Center alignment for specific columns
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
            header('Content-Disposition: attachment; filename="lendings_export_' . date('Y-m-d_His') . '.xlsx"');
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