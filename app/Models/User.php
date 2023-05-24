<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'niy',
        'email',
        'password',
        'jenis_user',
        'alamat',
        'no_hp',
        'pf_foto'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'pivot'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['link_foto'];

    public function getLinkFotoAttribute()
    {
        if ($this->pf_foto) {
            return Storage::disk('public')->url($this->pf_foto);
        } else {
            return url('/storage/profile/userdefault.jpg');
        }
    }

    public function ktgkaryawan()
    {
        return $this->belongsToMany(KategoriKaryawan::class, 'kategori_karyawan_users', 'user_id', 'kategori_id');
    }

    public function event()
    {
        return $this->belongsToMany(Event::class, 'pesertas', 'user_id', 'event_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'user_id', 'id')->orderByRaw('FIELD(jadwals.hari, "senin", "selasa", "rabu", "kamis", "jumat", "sabtu", "minggu")');
    }
}
