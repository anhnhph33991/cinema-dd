<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'movie_id',
        'payment_name',
        'total_price',
        'ticket_seat',
        'status',
    ];
}
