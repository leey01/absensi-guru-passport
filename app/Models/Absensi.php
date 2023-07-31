<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Absensi extends Model
{
    use HasFactory;

    protected $appends = ['link_foto_masuk', 'link_foto_pulang'];

    protected $fillable = [
        'user_id',
        'keterangan',
        'valid_masuk',
        'is_valid_masuk',
        'isvld_wkt_masuk',
        'waktu_masuk',
        'tanggal_masuk',
        'catatan_masuk',
        'foto_masuk',
        'lokasi_masuk',
        'longitude_masuk',
        'latitude_masuk',
        'valid_pulang',
        'is_valid_pulang',
        'isvld_wkt_pulang',
        'waktu_pulang',
        'tanggal_pulang',
        'catatan_pulang',
        'foto_pulang',
        'lokasi_pulang',
        'longitude_pulang',
        'latitude_pulang',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->with('ktgkaryawan');
    }

    public function getLinkFotoMasukAttribute()
    {
        if ($this->foto_masuk) {
            return Storage::disk('public')->url($this->foto_masuk);
        } else {
            return Storage::disk('public')->url('/image/no-image.png');
        }
    }

    public function getLinkFotoPulangAttribute()
    {
        if ($this->foto_pulang) {
            return Storage::disk('public')->url($this->foto_pulang);
        } else {
            return Storage::disk('public')->url('/image/no-image.png');
        }
    }


}
