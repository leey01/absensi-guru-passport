<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hari',
        'jam_masuk',
        'jam_pulang',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function jumlahKaryawan($tanggal)
    {
        $tanggalCarbon = Carbon::parse($tanggal)->isoFormat('dddd');
        $jmlKaryawan = Jadwal::where('hari', $tanggalCarbon)
            ->count();

        return $jmlKaryawan;
    }
}
