<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $tanggal = $request->tanggal ?? Carbon::now()->format('Y-m-d');

        // Karyawan
        $jmlKaryawan = User::all()
            ->count();

        // Jumlah masuk today
        $jmlMasuk = DB::table('absensis')
            ->where('keterangan', 'masuk')
            ->whereDate('created_at', $tanggal)
            ->count();
        // Jumlah pulang today
        $jmlPulang = DB::table('absensis')
            ->where('keterangan', 'pulang')
            ->whereDate('created_at', $tanggal)
            ->count();
        $jmlAbsen = $jmlKaryawan - ($jmlMasuk + $jmlPulang);


        $response = response()->json([
            'status' => 'success',
            'message' => 'Response default Dashboard',
            'data' => [
                'jml_kehadiran' => [
                    'jml_karyawan' => $jmlKaryawan,
                    'jml_masuk' => $jmlMasuk,
                    'jml_pulang' => $jmlPulang,
                    'jml_absen' => $jmlAbsen
                ]
            ]
        ]);

        return $response;
    }

    public function statistik()
    {
        // data 30 hari terakhir
//        $hariH = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d'))
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin1 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-1)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin2 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-2)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin3 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-3)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin4 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-4)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin5 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-5)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin6 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-6)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin7 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-7)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin8 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-8)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin9 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-9)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin10 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-10)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin11 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-11)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin12 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-12)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin13 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-13)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin14 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-14)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin15 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-15)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin16 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-16)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin17 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-17)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin18 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-18)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin19 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-19)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin20 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-20)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin21 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-21)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin22 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-22)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin23 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-23)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin24 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-24)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin25 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-25)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin26 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-26)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin27 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-27)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin28 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-28)
//            ->where('keterangan', 'pulang')
//            ->count();
//        $hMin29 = DB::table('absensis')
//            ->whereDay('created_at', Carbon::now()->format('d')-29)
//            ->where('keterangan', 'pulang')
//            ->count();
//
//        return response()->json([
//            'message' => 'data 7 hari terakhir',
//            'data' => [
//                'h' => $hariH,
//                'h-1' => $hMin1,
//                'h-2' => $hMin2,
//                'h-3' => $hMin3,
//                'h-4' => $hMin4,
//                'h-5' => $hMin5,
//                'h-6' => $hMin6,
//                'h-7' => $hMin7,
//                'h-8' => $hMin8,
//                'h-9' => $hMin9,
//                'h-10' => $hMin10,
//                'h-11' => $hMin11,
//                'h-12' => $hMin12,
//                'h-13' => $hMin13,
//                'h-14' => $hMin14,
//                'h-15' => $hMin15,
//                'h-16' => $hMin16,
//                'h-17' => $hMin17,
//                'h-18' => $hMin18,
//                'h-19' => $hMin19,
//                'h-20' => $hMin20,
//                'h-21' => $hMin21,
//                'h-22' => $hMin22,
//                'h-23' => $hMin23,
//                'h-24' => $hMin24,
//                'h-25' => $hMin25,
//                'h-26' => $hMin26,
//                'h-27' => $hMin27,
//                'h-28' => $hMin28,
//                'h-29' => $hMin29,
//            ]
//        ]);

        $data = DB::table('absensis')
            ->whereBetween('created_at', [Carbon::now()->subWeek()->format('Y-m-d'), Carbon::now()])
            ->where('keterangan', 'pulang')
            ->get()
            ->groupBy('tanggal_pulang');
        foreach ($data as $key=>$value){
            $data[$key]= [
                'date' => $key,
                'count' => $value->count()
            ];
        }

        return $data->values()->all();
    }

    public function jadwal()
    {
        $jadwalTerdekat = DB::table('kalenders')
            ->whereDate('tanggal','>=', Carbon::now()->format('Y-m-d'))
            ->get();

        return response()->json([
            'message' => 'jadwal terdekat',
            'data' => $jadwalTerdekat
        ]);
    }

    public function donloadKehadiran()
    {
        $start_time = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end_time = Carbon::now()->endOfMonth()->format('Y-m-d');
        $bulan = Carbon::now()->format('m');
//        return response()->json([
//           'start' => $start_time,
//           'end' => $end_time
//        ]);

        return Excel::download(new AbsensiExport($start_time, $end_time), "datakehadiran|bulan-$bulan.xlsx");
//        return (new AbsensiExport($start_time, $end_time))->download("datakehadiran|bulan-$bulan.xlsx");
    }
}
