<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcInspection extends Model
{
    protected $fillable = [
        'customer',
        'heat_number',
        'item',
        'result',
        'qc_operator_id',
        'qc_department_id',
        'inspected_at'
    ];

    public function operator()
    {
        return $this->belongsTo(QcOperator::class, 'qc_operator_id');
    }

    public function department()
    {
        return $this->belongsTo(QcDepartment::class, 'qc_department_id');
    }

    public function issues()
    {
        return $this->hasMany(QcIssue::class);
    }

    // scope cari heat number
    public function scopeSearchHeat($q, ?string $term)
    {
        return $term ? $q->where('heat_number', 'like', "%$term%") : $q;
    }

    // scope filter departemen
    public function scopeDept($q, $deptId)
    {
        return $deptId ? $q->where('qc_department_id', $deptId) : $q;
    }
}
