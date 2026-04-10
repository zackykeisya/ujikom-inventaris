<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Lending;
use Illuminate\Http\Request;
use App\Exports\LendingsExport;
use Maatwebsite\Excel\Facades\Excel;

class LendingController extends Controller
{
    public function index()
    {
        $lendings = Lending::with('item')->latest()->get();
        $items = Item::where('available', '>', 0)->get(); // Hanya item yang available > 0
        
        // Debug: Cek data items
        // dd($items); // Uncomment untuk debugging
        
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
        if (!$lending->return_date) {
            $item = $lending->item;
            $item->decrement('lending_total', $lending->total);
        }
        
        $lending->delete();
        return response()->json(['success' => true, 'message' => 'Peminjaman berhasil dihapus']);
    }

    public function export()
    {
        return Excel::download(new LendingsExport, 'lendings.xlsx');
    }
}