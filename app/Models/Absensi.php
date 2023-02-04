<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;


    protected $fillable = ['user_id', 'keterangan', 'waktu_masuk', 'tanggal_masuk', 'catatan_masuk', 'foto_masuk', 'lokasi_masuk', 'longitude_masuk', 'latitude_masuk'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
