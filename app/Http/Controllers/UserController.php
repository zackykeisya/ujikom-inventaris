<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UserController extends Controller
{
    public function index($role)
    {
        $users = User::where('role', $role)->orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users', 'role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,staff',
        ]);

        $emailPrefix = explode('@', $request->email)[0];
        $password = substr($emailPrefix, 0, 4) . rand(100, 999);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'User berhasil ditambahkan',
            'password' => $password
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'new_password' => 'nullable|min:4',
        ]);

        $data = $request->only(['name', 'email']);
        
        if ($request->filled('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);
        
        return response()->json(['success' => true, 'message' => 'User berhasil diupdate']);
    }

    public function resetPassword(User $user)
    {
        $emailPrefix = explode('@', $user->email)[0];
        $newPassword = substr($emailPrefix, 0, 4) . rand(100, 999);
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset',
            'password' => $newPassword
        ]);
    }

    public function destroy(User $user)
    {
        // Cegah menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false, 
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 422);
        }
        
        try {
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus user: ' . $e->getMessage()], 500);
        }
    }

    public function export($role)
    {
        try {
            // Ambil data users berdasarkan role
            $users = User::where('role', $role)->orderBy('created_at', 'desc')->get();
            
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Users - ' . ucfirst($role));
            
            // Set headers
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Name');
            $sheet->setCellValue('C1', 'Email');
            $sheet->setCellValue('D1', 'Role');
            $sheet->setCellValue('E1', 'Created At');
            $sheet->setCellValue('F1', 'Last Updated');
            
            // Style header
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
            
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            
            // Add data
            $row = 2;
            $no = 1;
            
            foreach ($users as $user) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $user->name);
                $sheet->setCellValue('C' . $row, $user->email);
                $sheet->setCellValue('D' . $row, $user->role);
                $sheet->setCellValue('E' . $row, $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-');
                $sheet->setCellValue('F' . $row, $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '-');
                
                // Color for role column
                if ($user->role == 'admin') {
                    $sheet->getStyle('D' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFC7CE']
                        ],
                        'font' => [
                            'color' => ['rgb' => '9C0006']
                        ]
                    ]);
                } else {
                    $sheet->getStyle('D' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'C6EFCE']
                        ],
                        'font' => [
                            'color' => ['rgb' => '006100']
                        ]
                    ]);
                }
                
                $row++;
                $no++;
            }
            
            // Apply borders to data cells
            $lastRow = $row - 1;
            if ($lastRow >= 2) {
                $sheet->getStyle('A2:F' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            
            // Set row height
            $sheet->getRowDimension(1)->setRowHeight(20);
            
            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="users_' . $role . '_' . date('Y-m-d_His') . '.xlsx"');
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