<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryNotif extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'is_read'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
