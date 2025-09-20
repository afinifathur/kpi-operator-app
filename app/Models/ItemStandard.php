<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStandard extends Model
{
    use HasFactory;
    protected $fillable = ['item_id','std_time_sec_per_pcs','aktif_dari','aktif_sampai'];
    protected $casts = ['aktif_dari'=>'date','aktif_sampai'=>'date','std_time_sec_per_pcs'=>'integer'];
    public function item(){ return $this->belongsTo(Item::class); }
}
