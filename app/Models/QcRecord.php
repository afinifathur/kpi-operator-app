<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcRecord extends Model
{
    use HasFactory;

    // Pastikan ke tabel yang benar (samakan dengan migrasinya: biasanya 'qc_records')
    protected $table = 'qc_records';

    protected $fillable = [
        'customer',
        'heat_number',
        'item',
        'qty',
        'defects',
        'operator',
        'qc_operator_id',
        'department',
        'notes',
    ];

    protected $casts = [
        'qty' => 'integer',
        'defects' => 'integer',
    ];
}
