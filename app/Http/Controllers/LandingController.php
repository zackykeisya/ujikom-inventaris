<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;

class LandingController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $totalItemsAvailable = Item::sum('available');
        $recentItems = Item::with('category')->latest()->take(6)->get();
        
        return view('landing', compact('totalItems', 'totalCategories', 'totalItemsAvailable', 'recentItems'));
    }
}