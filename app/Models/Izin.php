<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_izin',
        'mulai_izin',
        'selesai_izin',
        'deskripsi',
        'path_file'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
