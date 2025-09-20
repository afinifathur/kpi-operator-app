<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_mesin',
        'tipe',
        'lokasi',
        'status',
        'departemen',
    ];

    /**
     * Relasi: Machine memiliki banyak Job.
     */
    public function jobs(): HasMany
    {
        // FK eksplisit sesuai kolom di tabel jobs
        return $this->hasMany(Job::class, 'machine_id');
    }
}
