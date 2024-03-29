<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $request->tanggal ? $tanggal = $request->tanggal : $tanggal = Carbon::now()->format('Y-m-d');

        // Karyawan
        $jmlKaryawan = Jadwal::jumlahKaryawan($tanggal);

        // Jumlah masuk today
        $jmlMasuk = Absensi::kehadiran()
            ->where('tanggal_masuk', $tanggal)
            ->count();

        $jmlIzin = Absensi::izin()
            ->where('tanggal_masuk', $tanggal)
            ->count();

        $jmlAbsen = Absensi::absen()
            ->where('tanggal_masuk', $tanggal)
            ->count();
        $jmlAbsen = $jmlAbsen - $jmlIzin;

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

        $data_mingguan = Absensi::whereBetween('tanggal_masuk', [Carbon::now()->subWeek()->format('Y-m-d'), Carbon::now()->format('Y-m-d')])
            ->where('valid_masuk', '1')
            ->where('valid_pulang', '1')
            ->orderBy('tanggal_masuk', 'asc')
            ->get()
            ->groupBy('tanggal_masuk');

        foreach ($data_mingguan as $key=>$value){
            $data_mingguan[$key] = [
                'date' => $key,
                'count' => $value->count()
            ];
        }

        $data_bulanan = Absensi::whereBetween('tanggal_masuk', [Carbon::now()->subMonth()->format('Y-m-d'), Carbon::now()->format('Y-m-d')])
            ->where('valid_masuk', '1')
            ->where('valid_pulang', '1')
            ->orderBy('tanggal_masuk', 'asc')
            ->get()
            ->groupBy('tanggal_masuk');

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
                'bulanan' => $data_bulanan->values()->all(),
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
