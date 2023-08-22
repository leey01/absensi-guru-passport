<?php

namespace App\Http\Controllers;

use App\Events\NotifEvent;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\HistoryNotif;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\Setting;
use App\Models\User;
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

                HistoryNotif::create([
                    'user_id' => $peserta->id,
                    'event_id' => $event->id,
                ]);
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

    public function batasAbsen()
    {
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        $startOfDay = \Carbon\Carbon::now()->startOfDay();
        $endOfDay = \Carbon\Carbon::now()->endOfDay()->format('H:i:s');
        $waktu = '10:00:00';

        $waktu = explode(':', $waktu);
        $jam = $waktu[0];
        $menit = $waktu[1];
        $detik = $waktu[2];

        $jadwalMasuk = Carbon::parse('10:26:00');
        $jadwalPulang = '16:00:00';

        $batasWaktuMasuk = $jadwalMasuk->copy()->subHours($jam)->subMinutes($menit)->subSeconds($detik);
        $batasWaktuPulang = Carbon::parse($jadwalPulang)->addHours($jam)->addMinutes($menit)->addSeconds($detik)->format('H:i:s');

        if ($batasWaktuMasuk->isYesterday()) {
            $batasWaktuMasuk = $startOfDay;
        }

        // validasi waktu masuk
        // cek apakah jadwal masuk tersedia
        if ($jadwalMasuk) {
            // jika waktu sekarang kurang dari batas waktu masuk
            if ($now->lessThan($batasWaktuMasuk)) {
                return response()->json([
                    'message' => 'belum waktunya absen masuk',
                    'batas waktu masuk' => $batasWaktuMasuk->format('H:i:s'),
                ], 400);
            } else {
                // jika waktu sekarang kurang dari jadwal masuk maka akan true
                if ($now->lessThan($jadwalMasuk)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
            }

        } else {
            return response()->json([
                'message' => 'jadwal tidak ditemukan, tidak perlu absen'
            ], 404);
        }

        return response()->json([
            'batas waktu masuk' => $batasWaktuMasuk->format('H:i:s'),
            'valid' => $isValid,
        ]);
    }

    public function batasAbsenMasuk()
    {
        // waktu sekarang
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        //batas mulai absen masuk
        $time = Setting::where('key', 'batas_waktu_absen_masuk')->first();
        $time = explode(':', $time->value);

        // jadwal masuk user
        $hari = Carbon::now()->format('Y-m-d');
        $hari = Carbon::parse($hari)->locale('id');
        $hari->settings(['formatFunction' => 'translatedFormat']);
        $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
            ->where('hari', $hari->format('l'))
            ->first();
        $jadwalAbsen = Carbon::parse($jadwalAbsen->jam_masuk);

        // waktu minimal user bisa absen
        $modifiedTime = $jadwalAbsen->copy()->subHours($time[0])->subMinutes($time[1])->subSeconds($time[2]);

        // jika waktu minimal absen masuk berkurang melebihi tengah malam akan jadi 00:00
        if ($modifiedTime->isYesterday()) {
            $batasAbsen = Carbon::now()->startOfDay();
        } else {
            $batasAbsen = $modifiedTime;
        }

        // validasi waktu masuk
        // validasi klo ada jadwal absen hari ini
        if ($jadwalAbsen) {
            // jika waktu sekarang kurang dari batas waktu masuk
            if ($now->lessThan($batasAbsen)) {
                return response()->json([
                    'message' => 'belum waktunya absen masuk',
                    'batas waktu masuk' => $batasAbsen->format('H:i:s'),
                ], 400);
            } else {
                // jika waktu sekarang kurang dari jadwal masuk maka akan true
                if ($now->lessThan($jadwalAbsen)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
            }
        } else {
            return response()->json([
                'message' => 'jadwal tidak ditemukan, tidak perlu absen'
            ], 404);
        }

        return response()->json([
            'batas waktu masuk' => $batasAbsen->format('H:i:s'),
            'valid' => $isValid,
            'jadwal' => $jadwalAbsen->format('H:i:s'),
        ]);
    }

    // bikin fungsi seperti diatas tapi untuk pulang
    public function batasAbsenPulang()
    {
        // waktu sekarang
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        //batas mulai absen masuk
        $time = Setting::where('key', 'batas_waktu_absen_pulang')->first();
        $time = explode(':', $time->value);

        // jadwal pulang user
        $hari = Carbon::now()->format('Y-m-d');
        $hari = Carbon::parse($hari)->locale('id');
        $hari->settings(['formatFunction' => 'translatedFormat']);
        $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
            ->where('hari', $hari->format('l'))
            ->first();
        $jadwalAbsen = Carbon::parse($jadwalAbsen->jam_pulang);

        // waktu maksimal user bisa absen
        $modifiedTime = $jadwalAbsen->copy()->addHours($time[0])->addMinutes($time[1])->addSeconds($time[2]);

        // jika waktu maksimal absen pulang bertambah melebihi tengah malam akan jadi 23:59
        if ($modifiedTime->isTomorrow()) {
            $batasAbsen = Carbon::now()->endOfDay();
        } else {
            $batasAbsen = $modifiedTime;
        }

        // validasi waktu pulang
        // validasi klo ada jadwal absen hari ini
        if ($jadwalAbsen) {
            // jika waktu sekarang melebihi dari batas waktu pulang
            if ($now->greaterThan($batasAbsen)) {
                return response()->json([
                    'message' => 'waktu absen pulang sudah selesai',
                    'batas waktu pulang' => $batasAbsen->format('H:i:s'),
                ], 400);
            } else {
                // jika waktu sekarang melebihi dari jadwal pulang maka akan true
                if ($now->greaterThan($jadwalAbsen)) {
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
            }
        } else {
            return response()->json([
                'message' => 'jadwal tidak ditemukan, tidak perlu'
            ], 404);
        }

        return response()->json([
            'batas waktu pulang' => $batasAbsen->format('H:i:s'),
            'modified' => $modifiedTime->format('H:i:s'),
            'valid' => $isValid,
            'jadwal' => $jadwalAbsen->format('H:i:s'),
            'time' => $time,
        ]);
    }

    public function testAbsenLibur()
    {
        $today = Carbon::now()->format('Y-m-d');
        $users = [];

        // cari event yg hari ini libur
        $events = Event::with('peserta')
            ->where('kategori_event', 'libur')
            ->whereRaw('DATE(waktu_mulai) <= ?', [$today])
            ->whereRaw('DATE(waktu_selesai) >= ?', [$today])
            ->get();

        // ambil semua peserta yg ada di event libur
        foreach ($events as $event) {
            foreach ($event->peserta as $peserta) {
                $users[] = $peserta->id;
            }
        }

        $users = array_unique($users);

        foreach ($users as $user) {
            $absen = Absensi::create([
                'user_id' => $user,
                'keterangan' => 'libur',
                'is_valid_masuk' => null,
                'isvld_wkt_masuk' => null,
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
            'users' => $users,
        ]);
    }

    public function cekJadwal($hari)
    {
        $hari = Jadwal::where('hari', $hari)
            ->count();

        return $hari;
    }
}
