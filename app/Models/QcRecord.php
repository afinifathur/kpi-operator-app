<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer',
        'heat_number',
        'item',
        'qty',          // bagian ini yang ditambah
        'defects',      // bagian ini yang ditambah
        'hasil',        // biarkan untuk kompatibel (tidak dipakai untuk OK/NG lagi)
        'operator',
        'qc_operator_id', // bagian ini yang ditambah
        'department',
        'notes',
    ];

    public function qcOperator()
    {
        return $this->belongsTo(QcOperator::class, 'qc_operator_id');
    }
}
