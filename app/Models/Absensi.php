<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Absensi extends Model
{
    use HasFactory;

    protected $appends = ['link_foto_masuk', 'link_foto_pulang'];

    protected $fillable = ['user_id', 'keterangan', 'waktu_masuk', 'tanggal_masuk', 'catatan_masuk', 'foto_masuk', 'lokasi_masuk', 'longitude_masuk', 'latitude_masuk', 'is_valid_masuk', 'is_valid_pulang'];

    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getLinkFotoMasukAttribute()
    {
        if ($this->foto_masuk) {
            return Storage::disk('public')->url($this->foto_masuk);
        } else {
            return null;
        }
    }

    public function getLinkFotoPulangAttribute()
    {
        if ($this->foto_pulang) {
            return Storage::disk('public')->url($this->foto_pulang);
        } else {
            return null;
        }
    }


}
