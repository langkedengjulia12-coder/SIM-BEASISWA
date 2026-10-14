<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // Super Admin (Full Access)
        // ========================================
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@armada.com',
            'password' => Hash::make('admin123'),
        ]);
        $superAdmin->assignRole('Super Admin');

        // ========================================
        // Staff (Tidak bisa Delete)
        // ========================================
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@armada.com',
            'password' => Hash::make('staff123'),
        ]);
        $staff->assignRole('Staff');

        // ========================================
        // Public (Read Only)
        // ========================================
        $public = User::create([
            'name' => 'Public User',
            'email' => 'user@armada.com',
            'password' => Hash::make('public123'),
        ]);
        $public->assignRole('Public');
    }
}