<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin_produksi',
            'admin_produksi_fitting',
            'admin_produksi_flange',
            'supervisor_qa',
            'hr',
            'viewer',
        ];

        foreach ($roles as $r) {
            Role::findOrCreate($r);
        }

        // Contoh assign cepat (opsional, sesuaikan pengguna nyata Anda)
        $first = User::query()->orderBy('id')->first();
        if ($first) $first->syncRoles(['admin_produksi_fitting']); // contoh: user pertama untuk FITTING

        $second = User::query()->orderBy('id','desc')->first();
        if ($second && $second->isNot($first)) $second->syncRoles(['admin_produksi_flange']); // user terakhir untuk FLANGE
    }
}
