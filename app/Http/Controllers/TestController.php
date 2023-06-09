<?php

namespace App\Http\Controllers;

use App\Events\NotifEvent;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\Izin;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function testTimeNow()
    {
        $timeNow = time();
        $nowNow = now();
        $carbonNow = \Carbon\Carbon::now();

        return response()->json([
            'timeNow' => $timeNow,
            'nowNow' => $nowNow,
            'carbonNow' => $carbonNow,
            'format' => $carbonNow->format('Y-m-d H:i:s')
        ]);
    }

    public function testNotifEvent()
    {
        $events = Event::with('peserta')
            ->get();

        foreach ($events as $event) {
            foreach ($event->peserta as $peserta) {
                event(new NotifEvent($peserta->id, $event->id));
            }
        }

        return response()->json([
            'message' => 'event hari ini',
            'data' => $events
        ]);
    }

    public function testYgBlomAbsen()
    {
        $today = Carbon::now()->format('Y-m-d');

        // cari jadwal hari ini
        $jadwal = Jadwal::where('hari', Carbon::now()->isoFormat('dddd'))
            ->get();

        // cari user yg punya jadwal hari ini
        $userPunyaJadwal = [];
        foreach ($jadwal as $jwl) {
            array_push($userPunyaJadwal, $jwl->user_id);
        }

        // cari user yg sudah absen hari ini
        $absen = Absensi::where('tanggal_masuk', Carbon::now()->format('Y-m-d'))
            ->get();
        $userSudahAbsen = [];
        foreach ($absen as $abs) {
            array_push($userSudahAbsen, $abs->user_id);
        }

        // cari user yg sedang izin hari ini
        $izin = Izin::whereDate('mulai_izin', '<=', $today)
            ->whereDate('selesai_izin', '>=', $today)
            ->get();
        $userizin = [];
        foreach ($izin as $izn) {
            array_push($userizin, $izn->user_id);
        }

        // filter id yg duplicate
        $userSudahAbsen = array_unique($userSudahAbsen);
        $userizin = array_unique($userizin);
        $userYgGaAbsen = array_unique(array_merge($userSudahAbsen, $userizin));

        // cari user yg blom absen hari ini
        $userBlomAbsen = array_diff($userPunyaJadwal, $userYgGaAbsen);

        // sistem otomatis absen untuk user yg belum absen
        foreach ($userBlomAbsen as $user) {
            $absen = Absensi::create([
                'user_id' => $user,
                'is_valid_masuk' => 0,
                'isvld_wkt_masuk' => 0,
                'catatan_masuk' => '',
                'waktu_masuk' => '',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'foto_masuk' => '',
                'lokasi_masuk' => '',
                'longitude_masuk' => '',
                'latitude_masuk' => ''
            ]);
        }

        return response()->json([
            'message' => 'user yg blom absen',
            'userblomabsen' => $userBlomAbsen,
            'userSudahAbsen' => $userSudahAbsen,
            'users punya jadwal' => $userPunyaJadwal,
            'izin' => $userizin,
            'yg ga absen' => $userYgGaAbsen
        ]);
    }
}
