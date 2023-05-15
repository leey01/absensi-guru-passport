<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Izin extends Model
{
    use HasFactory;

    protected $appends = [
        'link_file'
    ];

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

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->with('ktgkaryawan');
    }

    public function getLinkFileAttribute()
    {
        if ($this->path_file == null) {
            return null;
        }
        return Storage::disk('public')->url($this->path_file);
    }
}
