<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StaffItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LendingController;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Auth
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    
    // Items
    Route::get('/items', [ItemController::class, 'index'])->name('admin.items');
    Route::post('/items', [ItemController::class, 'store'])->name('admin.items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('admin.items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('admin.items.destroy');
    Route::get('/items/export', [ItemController::class, 'export'])->name('admin.items.export');
    
    // TAMBAHKAN ROUTE INI UNTUK LENDING DETAILS
    Route::get('/items/{item}/lendings', [ItemController::class, 'getLendings'])->name('admin.items.lendings');

    // Users
    Route::get('/users/{role}', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::get('/users/export/{role}', [UserController::class, 'export'])->name('admin.users.export');
});

// Staff routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard');
    
    // Lendings
    Route::get('/lendings', [LendingController::class, 'index'])->name('staff.lendings');
    Route::post('/lendings', [LendingController::class, 'store'])->name('staff.lendings.store');
    Route::post('/lendings/{lending}/return', [LendingController::class, 'returnItem'])->name('staff.lendings.return');
    Route::delete('/lendings/{lending}', [LendingController::class, 'destroy'])->name('staff.lendings.destroy');
    Route::get('/lendings/export', [LendingController::class, 'export'])->name('staff.lendings.export');
    
    // Items - Gunakan StaffItemController
    Route::get('/items', [StaffItemController::class, 'index'])->name('staff.items');
    
    // Users
    Route::get('/users', function () {
        return view('staff.users');
    })->name('staff.users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('staff.users.update');
});