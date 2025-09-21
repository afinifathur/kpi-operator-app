<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Roles yang diizinkan oleh canAccessPanel() di User.php
        $roles = [
            'admin_produksi',
            'admin_produksi_fitting',
            'admin_produksi_flange',
            'supervisor_qa',
            'hr',
            'viewer',
        ];

        foreach ($roles as $r) {
            Role::findOrCreate($r, 'web'); // guard 'web' supaya hasAnyRole() bekerja
        }

        // Buat / update user admin default
        $user = User::updateOrCreate(
            ['email' => 'admin@peroni.local'],
            [
                'name' => 'Admin QC',
                'password' => Hash::make('SandiKuat123!'),
                'email_verified_at' => now(),
            ]
        );

        // Berikan salah satu role yang diizinkan panel
        $user->syncRoles(['hr']);
    }
}
