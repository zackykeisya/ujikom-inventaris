<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

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
        
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User berhasil dihapus']);
    }

    public function export($role)
    {
        return Excel::download(new UsersExport($role), 'users_' . $role . '.xlsx');
    }
}