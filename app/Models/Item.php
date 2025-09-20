<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;
    protected $fillable = ['kode_barang','nama_barang','size','aisi','cust','catatan'];

    public function standards(){ return $this->hasMany(ItemStandard::class); }

    public function activeStandardAt($date){
        return $this->standards()
            ->where('aktif_dari','<=',$date)
            ->where(function($q) use ($date){ $q->whereNull('aktif_sampai')->orWhere('aktif_sampai','>=',$date); })
            ->orderByDesc('aktif_dari')
            ->first();
    }
	  public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'item_id'); // FK: jobs.item_id
    }
}
