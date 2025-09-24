<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcOperator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'active',
    ];

    // relasi balik (opsional)
    public function records()
    {
        return $this->hasMany(QcRecord::class, 'qc_operator_id');
    }
}
