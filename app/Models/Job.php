<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'operator_id',
        'item_id',
        'machine_id',
        'shift_id',
        'jam_mulai',
        'jam_selesai',
        'qty_hasil',
        'timer_sec_per_pcs',
        'sumber_timer',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'     => 'date',
            'jam_mulai'   => 'datetime',
            'jam_selesai' => 'datetime',
        ];
    }

    // === Relasi untuk Filament ===
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function evaluation()
    {
        return $this->hasOne(JobEvaluation::class);
    }
}
