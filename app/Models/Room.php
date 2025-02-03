<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    public const MATRIX = [
        'id' => 1,
        'name' => '12x12',
        'max_row' => 12,
        'max_col' => 12,
        'description' => 'Sức chứa tối đa 144 chỗ ngồi.',
        'row_default' => ['regular' => 8, 'vip' => 4]
    ];

    protected $fillable = [
        'movie_id',
        'name',
        'seat_structures',
        'is_active',
        'surcharge',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
