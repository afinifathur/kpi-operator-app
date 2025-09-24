<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QcOperator;

class QcOperatorSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Budi',    'department' => 'Netto'],
            ['name' => 'Sari',    'department' => 'Bubut'],
            ['name' => 'Andi',    'department' => 'Cor'],
            ['name' => 'Dewi',    'department' => 'Finishing'],
        ];

        foreach ($rows as $r) {
            QcOperator::firstOrCreate(
                ['name' => $r['name'], 'department' => $r['department']],
                ['active' => true]
            );
        }
    }
}
