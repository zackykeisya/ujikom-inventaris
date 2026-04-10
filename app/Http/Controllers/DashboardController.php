<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Lending;
use App\Models\Category;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $totalUsers = User::count();
        $totalLendings = Lending::count();
        $activeLendings = Lending::whereNull('return_date')->count();
        
        return view('admin.dashboard', compact('totalItems', 'totalCategories', 'totalUsers', 'totalLendings', 'activeLendings'));
    }

    public function staffDashboard()
    {
        $totalItems = Item::count();
        $totalLendings = Lending::count();
        $activeLendings = Lending::whereNull('return_date')->count();
        $recentLendings = Lending::with('item')->latest()->take(5)->get();
        
        return view('staff.dashboard', compact('totalItems', 'totalLendings', 'activeLendings', 'recentLendings'));
    }
}