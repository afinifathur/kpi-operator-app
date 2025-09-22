<?php
// bagian ini yang ditambah

namespace Database\Seeders;

use App\Models\QcRecord;
use Illuminate\Database\Seeder;

class QcRecordSeeder extends Seeder
{
    public function run(): void
    {
        // idempotent: jika sudah ada data, lewati
        if (QcRecord::count() > 0) {
            return;
        }

        QcRecord::insert([
            [
                'customer'   => 'PT Sukses Makmur',
                'heat_number'=> 'HN-240901-001',
                'item'       => 'Flange 2" 150#',
                'hasil'      => 'OK',
                'operator'   => 'Budi',
                'department' => 'QC',
                'notes'      => 'Sampling awal OK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer'   => 'CV Baja Prima',
                'heat_number'=> 'HN-240901-002',
                'item'       => 'Elbow 3" SCH40',
                'hasil'      => 'NG',
                'operator'   => 'Sari',
                'department' => 'QC',
                'notes'      => 'Ovality melebihi toleransi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
