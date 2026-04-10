<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('items')->get();
        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories|max:255',
            'division_pj' => 'required|in:Sarpras,Tata Usaha,Tefa'
        ]);

        Category::create($request->all());
        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,' . $category->id . '|max:255',
            'division_pj' => 'required|in:Sarpras,Tata Usaha,Tefa'
        ]);

        $category->update($request->all());
        return response()->json(['success' => true, 'message' => 'Kategori berhasil diupdate']);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    }
}