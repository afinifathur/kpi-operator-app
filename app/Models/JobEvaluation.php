<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobEvaluation extends Model
{
    use HasFactory;
    protected $fillable = ['job_id','target_qty','pencapaian_pct','kategori','auto_flag'];
    public function job(){ return $this->belongsTo(Job::class); }
}
