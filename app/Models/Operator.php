<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_induk',
        'nama',
        'departemen',
        'status_aktif',
    ];

    /**
     * Relasi: Operator memiliki banyak Job.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'operator_id');
    }
}
