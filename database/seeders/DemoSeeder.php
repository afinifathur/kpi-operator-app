<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Operator, Machine, Item, ItemStandard, Shift, Setting, User};
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Settings
        Setting::updateOrCreate(['key'=>'near_threshold_pct'], ['value'=>'80']);
        Setting::updateOrCreate(['key'=>'shift_minutes_default'], ['value'=>'420']);

        // Operators
        foreach (range(1,5) as $i){
            Operator::updateOrCreate(
                ['no_induk' => sprintf('OP%03d', $i)],
                ['nama' => 'Operator '.$i, 'departemen'=>'Produksi','status_aktif'=>true]
            );
        }

        // Machines
        foreach (range(1,3) as $i){
            Machine::updateOrCreate(['no_mesin'=>sprintf('MC%02d', $i)], ['tipe'=>'CNC','lokasi'=>'L1','status'=>'AKTIF']);
        }

        // Items + Standards
        foreach (range(1,5) as $i){
            $item = Item::updateOrCreate(['kode_barang'=>sprintf('ITM%03d', $i)], [
                'nama_barang'=>"Produk $i", 'size'=>'1/2"', 'aisi'=>'304', 'cust'=>'CUSTX'
            ]);
            ItemStandard::updateOrCreate([
                'item_id'=>$item->id, 'aktif_dari'=>now()->subYear()->toDateString()
            ], ['std_time_sec_per_pcs'=>120, 'aktif_sampai'=>null]);
        }

        // Shift
        Shift::updateOrCreate(['nama'=>'Shift A'], ['work_minutes'=>420, 'mulai_default'=>'08:00:00','selesai_default'=>'17:00:00']);

        // Admin user (Breeze to be installed separately)
        if (!User::where('email','admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password')
            ]);
        }
    }
}
