<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'img_thumbnail',
        'description',
        'duration',
        'release_date',
        'end_date',
        'trailer_url',
        'is_active',
    ];
}
