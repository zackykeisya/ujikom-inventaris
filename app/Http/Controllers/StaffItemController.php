<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;

class StaffItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->get();
        return view('staff.items', compact('items'));
    }
}