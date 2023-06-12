<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAdmin extends Model
{
    use HasFactory;

    protected $table = 'role_admins';

    protected $fillable = [
        'kategori_id',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKaryawan::class, 'kategori_id', 'id');
    }
}
