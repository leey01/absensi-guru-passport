<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'judul', 'kategori_event', 'lokasi', 'waktu_mulai', 'waktu_selesai', 'deskripsi'];

    protected $hidden = ['created_at', 'updated_at', 'pivot', 'user_id'];

    public function peserta()
    {
        return $this->belongsToMany(User::class, 'pesertas', 'event_id', 'user_id')->with('ktgkaryawan');
    }

}
