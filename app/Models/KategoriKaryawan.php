<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKaryawan extends Model
{
    use HasFactory;

    protected $fillable = ['kategori'];

    protected $hidden = ['created_at', 'updated_at', 'pivot'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'kategori_karyawan_users', 'kategori_id', 'user_id');
    }
}
