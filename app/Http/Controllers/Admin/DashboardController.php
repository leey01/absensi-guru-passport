<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\Izin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $tanggal = Carbon::now()->format('Y-m-d');

        // Karyawan
        $jmlKaryawan = User::all()
            ->count();

        // Jumlah masuk today
        $jmlMasuk = Absensi::where('is_valid_masuk', '1')
            ->where('isvld_wkt_masuk', '1')
            ->whereDate('tanggal_masuk', $tanggal)
            ->count();
        $jmlIzin = Izin::whereDate('mulai_izin', '<=', $tanggal)
            ->whereDate('selesai_izin', '>=', $tanggal)
            ->count();
        $jmlAbsen = $jmlKaryawan - (Absensi::where('keterangan', 'masuk')
                ->whereDate('tanggal_masuk', $tanggal)
                ->count());

        $response = response()->json([
            'message' => 'success',
            'data' => [
                'jumlah_karyawan' => $jmlKaryawan,
                'jumlah_masuk' => $jmlMasuk,
                'jumlah_izin' => $jmlIzin,
                'jumlah_absen' => $jmlAbsen,
                'tanggal' => $tanggal
            ]
        ]);

        return $response;
    }

    public function statistik()
    {

        $data_mingguan = Absensi::whereBetween('created_at', [Carbon::now()->subWeek()->format('Y-m-d'), Carbon::now()])
            ->where('is_valid_pulang', '1')
            ->where('isvld_wkt_pulang', '1')
            ->orderBy('tanggal_pulang', 'asc')
            ->get()
            ->groupBy('tanggal_pulang');

        foreach ($data_mingguan as $key=>$value){
            $data_mingguan[$key] = [
                'date' => $key,
                'count' => $value->count()
            ];
        }

        $data_bulanan = Absensi::whereBetween('created_at', [Carbon::now()->subMonth()->format('Y-m-d'), Carbon::now()])
            ->where('is_valid_pulang', '1')
            ->where('isvld_wkt_pulang', '1')
            ->orderBy('tanggal_pulang', 'asc')
            ->get()
            ->groupBy('tanggal_pulang');

        foreach ($data_bulanan as $key=>$value){
            $data_bulanan[$key] = [
                'date' => $key,
                'count' => $value->count()
            ];
        }


//        return $data->values()->all();
        return response()->json([
            'message' => 'success',
            'data' => [
                'mingguan' => $data_mingguan->values()->all(),
                'bulanan' => $data_bulanan->values()->all()
            ]
        ]);
    }

    public function jadwal()
    {
        $jadwalTerdekat = Event::whereDate('waktu_mulai','>=', Carbon::now())
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => $jadwalTerdekat
        ]);
    }

}
