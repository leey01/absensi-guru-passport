<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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

    // valid masuk 1
    // valid pulang 1
//    public static function kehadiran($startMonth, $endMonth)
//    {
//
//        $data = Absensi::where('user_id', Auth::user()->id)
//            ->where('valid_masuk', '1')
//            ->where('valid_pulang', '1')
//            ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
//            ->get();
//
//        return $data ?? null;
//    }

    public static function kehadiran()
    {

        $data = Absensi::where('valid_masuk', '1')
            ->where('valid_pulang', '1')
            ->get();

        return $data ?? null;
    }

//    public static function absen($user, $startMonth, $endMonth)
//    {
//
//        $data = Absensi::where(function ($query) use ($startMonth, $endMonth, $user) {
//            $query->where('valid_masuk', '0')
//                ->where('valid_pulang', '0')
//                ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
//                ->where('user_id', $user->id);
//        })->orWhere(function ($query) use ($startMonth, $endMonth, $user) {
//            $query->where('valid_masuk', '0')
//                ->where('valid_pulang', '1')
//                ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
//                ->where('user_id', $user->id);
//        })->orWhere(function ($query) use ($startMonth, $endMonth, $user) {
//            $query->where('valid_masuk', '1')
//                ->where('valid_pulang', '0')
//                ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
//                ->where('user_id', $user->id);
//        })->get();
//
//        return $data ?? null;
//    }

    public static function absen()
    {

        $data = Absensi::where(function ($query) {
            $query->where('valid_masuk', '0')
                ->where('valid_pulang', '0');
        })->orWhere(function ($query) {
            $query->where('valid_masuk', '0')
                ->where('valid_pulang', '1');
        })->orWhere(function ($query) {
            $query->where('valid_masuk', '1')
                ->where('valid_pulang', '0');
        })->get();

        return $data ?? null;
    }

//    public static function izin($user, $startMonth, $endMonth)
//    {
//        $data = Absensi::where('user_id', $user->id)
//            ->where('keterangan', 'izin')
//            ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
//            ->get();
//
//        return $data ?? null;
//    }

    public static function izin()
    {
        $data = Absensi::where('keterangan', 'izin')
            ->get();

        return $data ?? null;
    }

}
