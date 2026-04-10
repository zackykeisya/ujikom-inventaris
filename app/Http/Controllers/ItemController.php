<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Exports\ItemsExport;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{

    public function index()
    {
        $items = Item::with('category')->get();
        $categories = Category::all();
        
        // Debug: Cek apakah ada data
        //  dd($categories); // Uncomment untuk debugging
        
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
        return Excel::download(new ItemsExport, 'items.xlsx');
    }
}