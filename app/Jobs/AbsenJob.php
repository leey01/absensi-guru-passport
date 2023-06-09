<?php

namespace App\Jobs;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jadwal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class AbsenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
    }
}
