<?php
// bagian ini yang ditambah

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer',
        'heat_number',
        'item',
        'hasil',
        'operator',
        'department',
        'notes',
    ];

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) {
            return $q;
        }
        $like = '%'.$term.'%';

        return $q->where(function ($w) use ($like) {
            $w->where('heat_number', 'like', $like)
              ->orWhere('customer', 'like', $like)
              ->orWhere('item', 'like', $like)
              ->orWhere('operator', 'like', $like)
              ->orWhere('department', 'like', $like)
              ->orWhere('hasil', 'like', $like);
        });
    }
}
