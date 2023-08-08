<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
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


    public static function kehadiran()
    {

        $data = Absensi::where('valid_masuk', '1')
            ->where('valid_pulang', '1')
            ->get();

        return $data ?? null;
    }


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


    public static function izin()
    {
        $data = Absensi::where('keterangan', 'izin')
            ->get();

        return $data ?? null;
    }

    public static function simpanFoto($foto, $tipe)
    {
        try {
            $fotoName = time() . $foto->getClientOriginalName();
            $fotoPath = $tipe .'/'. $fotoName;
            Storage::disk('public')->put($fotoPath, file_get_contents($foto));
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed save photo",
                'data' => $e
            ], 503);
        }

        return $fotoPath;
    }

    public static function batasAbsenMasuk($user_id)
    {
        //batas mulai absen masuk
        $time = Setting::where('key', 'batas_waktu_absen_masuk')->first();
        $time = explode(':', $time->value);

        // jadwal masuk user
        $hari = Carbon::now()->format('Y-m-d');
        $hari = Carbon::parse($hari)->locale('id');
        $hari->settings(['formatFunction' => 'translatedFormat']);
        $jadwalAbsen = Jadwal::where('user_id', $user_id)
            ->where('hari', $hari->format('l'))
            ->first();

        if (!$jadwalAbsen) {
            return [
                'batas_absen' => false,
                'jadwal_absen' => false
            ];
        }

        $jadwalAbsen = Carbon::parse($jadwalAbsen->jam_masuk);

        // waktu minimal user bisa absen
        $modifiedTime = $jadwalAbsen->copy()->subHours($time[0])->subMinutes($time[1])->subSeconds($time[2]);

        // jika waktu minimal absen masuk berkurang melebihi tengah malam akan jadi 00:00
        if ($modifiedTime->isYesterday()) {
            $batasAbsen = Carbon::now()->startOfDay();
        } else {
            $batasAbsen = $modifiedTime;
        }

        return [
            'batas_absen' => $batasAbsen,
            'jadwal_absen' => $jadwalAbsen
        ];
    }

    public static function validasiMasuk($jadwal, $now)
    {
        $jadwalAbsen = Carbon::parse($jadwal['jadwal_absen']);
        $batasAbsen = Carbon::parse($jadwal['batas_absen']);

        // validasi waktu masuk
        // jika waktu sekarang kurang dari batas waktu masuk
        if ($now->lessThan($batasAbsen)) {
                $data['waktu_minimal_absen'] = $batasAbsen->format('H:i:s');
                $data['message'] = 'belum waktunya absen masuk';
                // apakah waktu sudah masuk ke jadwal
                $data['status_waktu'] = false;
        } else {
            // jika waktu sekarang kurang dari jadwal masuk maka akan true
            if ($now->lessThan($jadwalAbsen)) {
                $data['validasi_waktu'] = 1;
                $data['message'] = 'absen masuk berhasil';
                // apakah waktu sudah masuk ke jadwal
                $data['status_waktu'] = true;
            } else {
                $data['validasi_waktu'] = 0;
                $data['message'] = 'absen masuk terlambat';
                // apakah waktu sudah masuk ke jadwal
                $data['status_waktu'] = true;
            }
        }

        return $data;
    }

    public static function batasAbsenPulang($user_id)
    {
        //batas mulai absen masuk
        $time = Setting::where('key', 'batas_waktu_absen_pulang')->first();
        $time = explode(':', $time->value);

        // jadwal pylang user
        $hari = Carbon::now()->format('Y-m-d');
        $hari = Carbon::parse($hari)->locale('id');
        $hari->settings(['formatFunction' => 'translatedFormat']);
        $jadwalAbsen = Jadwal::where('user_id', $user_id)
            ->where('hari', $hari->format('l'))
            ->first();

        if (!$jadwalAbsen) {
            return [
                'batas_absen' => false,
                'jadwal_absen' => false
            ];
        }

        $jadwalAbsen = Carbon::parse($jadwalAbsen->jam_pulang);

        // waktu maksimal user bisa absen
        $modifiedTime = $jadwalAbsen->copy()->addHours($time[0])->addMinutes($time[1])->addSeconds($time[2]);

        // jika waktu maksimal absen pulang bertambah melebihi tengah malam akan jadi 23:59
        if ($modifiedTime->isTomorrow()) {
            $batasAbsen = Carbon::now()->endOfDay();
        } else {
            $batasAbsen = $modifiedTime;
        }

        return [
            'batas_absen' => $batasAbsen,
            'jadwal_absen' => $jadwalAbsen
        ];
    }

    public static function validasiPulang($jadwal, $now)
    {
        $jadwalAbsen = Carbon::parse($jadwal['jadwal_absen']);
        $batasAbsen = Carbon::parse($jadwal['batas_absen']);

        // jika waktu sekarang melebihi dari batas waktu pulang
        if ($now->greaterThan($batasAbsen)) {
            $data['validasi_waktu'] = 0;
            $data['message'] = 'waktu absen pulang sudah selesai';
        } else {
            // jika waktu sekarang melebihi dari jadwal pulang maka akan true
            if ($now->greaterThan($jadwalAbsen)) {
                $data['validasi_waktu'] = 1;
                $data['message'] = 'absen pulang berhasil';
            } else {
                $data['validasi_waktu'] = 0;
                $data['message'] = 'waktu absen pulang belum tiba';
            }
        }

        return $data;
    }

    public static function statusIzin($user_id)
    {
        $today = Carbon::now()->format('Y-m-d');

        $izin = Izin::whereDate('mulai_izin', '<=', $today)
            ->whereDate('selesai_izin', '>=', $today)
            ->where('user_id', $user_id)
            ->get();

        if ($izin) {
            return true;
        } else {
            return false;
        }
    }

}
