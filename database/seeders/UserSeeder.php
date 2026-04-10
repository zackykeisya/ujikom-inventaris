<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel users terlebih dahulu (opsional)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // User::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Data users sesuai struktur migration
        $users = [
            // Admin Users
            [
                'name' => 'Admin Utama',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin Sarpras',
                'email' => 'admin.sarpras@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin Tata Usaha',
                'email' => 'admin.tu@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin Tefa',
                'email' => 'admin.tefa@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Staff Users
            [
                'name' => 'Staff Perpustakaan',
                'email' => 'staff.perpus@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Laboratorium',
                'email' => 'staff.lab@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Gudang',
                'email' => 'staff.gudang@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Sarpras',
                'email' => 'staff.sarpras@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Tata Usaha',
                'email' => 'staff.tu@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Tefa',
                'email' => 'staff.tefa@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Operasional',
                'email' => 'staff.ops@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Keuangan',
                'email' => 'staff.keuangan@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert users
        foreach ($users as $user) {
            User::create($user);
        }

        // Tampilkan informasi di console
        $this->command->info('✅ User seeded successfully!');
        $this->command->info('📊 Total users: ' . count($users));
        $this->command->info('👑 Admin users: ' . User::where('role', 'admin')->count());
        $this->command->info('👥 Staff users: ' . User::where('role', 'staff')->count());
        $this->command->info('');
        $this->command->info('🔑 Default login credentials:');
        $this->command->info('   ┌─────────────────┬──────────────────────────────┬──────────┐');
        $this->command->info('   │ Role            │ Email                        │ Password │');
        $this->command->info('   ├─────────────────┼──────────────────────────────┼──────────┤');
        $this->command->info('   │ Admin           │ admin@example.com            │ password │');
        $this->command->info('   │ Admin           │ admin.sarpras@example.com    │ password │');
        $this->command->info('   │ Admin           │ admin.tu@example.com         │ password │');
        $this->command->info('   │ Admin           │ admin.tefa@example.com       │ password │');
        $this->command->info('   ├─────────────────┼──────────────────────────────┼──────────┤');
        $this->command->info('   │ Staff           │ staff.perpus@example.com     │ password │');
        $this->command->info('   │ Staff           │ staff.lab@example.com        │ password │');
        $this->command->info('   │ Staff           │ staff.gudang@example.com     │ password │');
        $this->command->info('   │ Staff           │ staff.sarpras@example.com    │ password │');
        $this->command->info('   │ Staff           │ staff.tu@example.com         │ password │');
        $this->command->info('   │ Staff           │ staff.tefa@example.com       │ password │');
        $this->command->info('   │ Staff           │ staff.ops@example.com        │ password │');
        $this->command->info('   │ Staff           │ staff.keuangan@example.com   │ password │');
        $this->command->info('   └─────────────────┴──────────────────────────────┴──────────┘');
    }
}