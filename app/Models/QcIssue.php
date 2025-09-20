<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcIssue extends Model
{
    protected $fillable = [
        'qc_inspection_id',
        'qc_operator_id',
        'qc_department_id',
        'issue_count',
        'notes'
    ];

    public function inspection()
    {
        return $this->belongsTo(QcInspection::class);
    }

    public function operator()
    {
        return $this->belongsTo(QcOperator::class, 'qc_operator_id');
    }

    public function department()
    {
        return $this->belongsTo(QcDepartment::class, 'qc_department_id');
    }
}
