<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['key','value'];
	
	 // Helper cache + get/put
    public static function getValue(string $key, $default = null)
    {
        return cache()->remember("setting:$key", 3600, function () use ($key, $default) {
            $row = static::query()->where('key',$key)->value('value');
            return $row ?? $default;
        });
    }
	public static function putValue(string $key, $value): void
    {
        static::updateOrCreate(['key'=>$key], ['value'=>$value]);
        cache()->forget("setting:$key");
    }
    public static function get(string $key, $default=null){
        $row = static::where('key',$key)->first();
        return $row ? $row->value : $default;
    }
}
