<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcDepartment extends Model
{
    protected $fillable = ['name'];

    public function operators()
    {
        return $this->hasMany(QcOperator::class);
    }
}
