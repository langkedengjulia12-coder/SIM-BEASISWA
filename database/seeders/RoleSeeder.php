<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions untuk semua modul
        $permissions = [
            // General/System
            'manage users', 'manage system settings', 'view audit log',
            // Finance
            'view finance', 'manage finance',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ========================================
        // Role 1: Super Admin (Full Access)
        // ========================================
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // ========================================
        // Role 2: Staff (Tidak bisa Delete)
        // ========================================
        $staff = Role::firstOrCreate(['name' => 'Staff']);
        $staff->givePermissionTo([
            // Finance (terbatas)
            'view finance',
        ]);

        // ========================================
        // Role 3: Admin (SIMK Manager)
        // ========================================
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // ========================================
        // Role 4: Pengelola
        // ========================================
        $pengelola = Role::firstOrCreate(['name' => 'pengelola']);
        $pengelola->givePermissionTo([
            'view finance', 'manage finance',
        ]);

        // ========================================
        // Role 5: Penyewa
        // ========================================
        $penyewa = Role::firstOrCreate(['name' => 'penyewa']);
        $penyewa->givePermissionTo([
        ]);

        // ========================================
        // Role 6: Public (Read Only)
        // ========================================
        $public = Role::firstOrCreate(['name' => 'Public']);
        $public->givePermissionTo([
        ]);
    }
}