<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\Izin;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AutoAbsenController extends Controller
{
    public function index()
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

        // cari user yg sedang libur hari ini
        // cari event yg hari ini libur
        $usersLibur = [];
        $events = Event::with('peserta')
            ->where('kategori_event', 'libur')
            ->whereRaw('DATE(waktu_mulai) <= ?', [$today])
            ->whereRaw('DATE(waktu_selesai) >= ?', [$today])
            ->get();

        // ambil semua peserta yg ada di event libur
        foreach ($events as $event) {
            foreach ($event->peserta as $peserta) {
                $usersLibur[] = $peserta->id;
            }
        }

        // filter id yg duplicate
        $userSudahAbsen = array_unique($userSudahAbsen);
        $userizin = array_unique($userizin);
        $usersLibur = array_unique($usersLibur);
        $userYgGaAbsen = array_unique(array_merge($userSudahAbsen, $userizin, $usersLibur));

        // cari user yg blom absen hari ini
        $userBlomAbsen = array_diff($userPunyaJadwal, $userYgGaAbsen);

        // sistem otomatis absen untuk user yg belum absen
        foreach ($userBlomAbsen as $user) {
            $absen = Absensi::create([
                'user_id' => $user,
                'keterangan' => 'alpha',
                'valid_masuk' => 0,
                'is_valid_masuk' => 0,
                'isvld_wkt_masuk' => 0,
                'catatan_masuk' => '',
                'waktu_masuk' => '',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'foto_masuk' => '/image/no-image.png',
                'lokasi_masuk' => '',
                'longitude_masuk' => '',
                'latitude_masuk' => '',
                'valid_pulang' => 0,
                'is_valid_pulang' => 0,
                'isvld_wkt_pulang' => 0,
                'catatan_pulang' => '',
                'waktu_pulang' => '',
                'tanggal_pulang' => Carbon::now()->format('Y-m-d'),
                'foto_pulang' => '/image/no-image.png',
                'lokasi_pulang' => '',
                'longitude_pulang' => '',
                'latitude_pulang' => '',
            ]);
        }

        // sistem otomatis absen untuk user yg sedang izin
        foreach ($userizin as $user) {
            $absen = Absensi::create([
                'user_id' => $user,
                'keterangan' => 'izin',
                'is_valid_masuk' => 1,
                'isvld_wkt_masuk' => 1,
                'catatan_masuk' => 'Izin',
                'waktu_masuk' => Carbon::now()->format('H:i:s'),
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'foto_masuk' => '/image/no-image.png',
                'lokasi_masuk' => '',
                'longitude_masuk' => '',
                'latitude_masuk' => '',
                'valid_pulang' => 0,
                'is_valid_pulang' => 0,
                'isvld_wkt_pulang' => 0,
                'catatan_pulang' => '',
                'waktu_pulang' => '',
                'tanggal_pulang' => Carbon::now()->format('Y-m-d'),
                'foto_pulang' => '/image/no-image.png',
                'lokasi_pulang' => '',
                'longitude_pulang' => '',
                'latitude_pulang' => '',
            ]);
        }

        // sistem otomatis absen untuk user yg sedang libur
        foreach ($usersLibur as $user) {
            $absen = Absensi::create([
                'user_id' => $user,
                'keterangan' => 'libur',
                'valid_masuk' => null,
                'is_valid_masuk' => null,
                'isvld_wkt_masuk' => null,
                'catatan_masuk' => '',
                'waktu_masuk' => '',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'foto_masuk' => '/image/no-image.png',
                'lokasi_masuk' => '',
                'longitude_masuk' => '',
                'latitude_masuk' => '',
                'valid_pulang' => 0,
                'is_valid_pulang' => 0,
                'isvld_wkt_pulang' => 0,
                'catatan_pulang' => '',
                'waktu_pulang' => '',
                'tanggal_pulang' => Carbon::now()->format('Y-m-d'),
                'foto_pulang' => '/image/no-image.png',
                'lokasi_pulang' => '',
                'longitude_pulang' => '',
                'latitude_pulang' => '',
            ]);
        }
    }

    public function getStatusKehadiranUser()
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

        // cari user yg sedang libur hari ini
        // cari event yg hari ini libur
        $usersLibur = [];
        $events = Event::with('peserta')
            ->where('kategori_event', 'libur')
            ->whereRaw('DATE(waktu_mulai) <= ?', [$today])
            ->whereRaw('DATE(waktu_selesai) >= ?', [$today])
            ->get();

        // ambil semua peserta yg ada di event libur
        foreach ($events as $event) {
            foreach ($event->peserta as $peserta) {
                $usersLibur[] = $peserta->id;
            }
        }

        // filter id yg duplicate
        $userSudahAbsen = array_unique($userSudahAbsen);
        $userizin = array_unique($userizin);
        $usersLibur = array_unique($usersLibur);
        $userYgGaAbsen = array_unique(array_merge($userSudahAbsen, $userizin, $usersLibur));

        // cari user yg blom absen hari ini
        $userBlomAbsen = array_diff($userPunyaJadwal, $userYgGaAbsen);

        return [
            'userPunyaJadwal' => $userPunyaJadwal,
            'userSudahAbsen' => $userSudahAbsen,
            'userizin' => $userizin,
            'usersLibur' => $usersLibur,
            'userYgGaAbsen' => $userYgGaAbsen,
            'userBlomAbsen' => $userBlomAbsen,
        ];
    }
}
