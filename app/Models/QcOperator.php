<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcOperator extends Model
{
    protected $fillable = ['name', 'qc_department_id'];

    public function department()
    {
        return $this->belongsTo(QcDepartment::class, 'qc_department_id');
    }

    public function inspections()
    {
        return $this->hasMany(QcInspection::class, 'qc_operator_id');
    }

    public function issues()
    {
        return $this->hasMany(QcIssue::class, 'qc_operator_id');
    }
}
