<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hari',
        'jam_masuk',
        'jam_pulang',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];
}
