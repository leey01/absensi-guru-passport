<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\KehadiranResource;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use function PHPUnit\Framework\isNull;
use function PHPUnit\Framework\isTrue;

class KehadiranController extends Controller
{
    // fungsi ga dipake
    public function kehadirannn(){
        // Karyawan
        $jmlKaryawan = User::all()
            ->count();

        // Jumlah masuk today
        $jmlMasuk = DB::table('absensis')
            ->where('keterangan', 'masuk')
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->count();
        // Jumlah pulang today
        $jmlPulang = DB::table('absensis')
            ->where('keterangan', 'pulang')
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->count();
        $jmlAbsen = $jmlKaryawan - $jmlMasuk;

        // list absen masuk pulang dengan paginate
        $listMasuk = Absensi::with(['user'])
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->where('keterangan', 'masuk')
            ->paginate(12);

        $listPulang = Absensi::with(['user'])
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->where('keterangan', 'pulang')
            ->paginate(12);


        $response = response()->json([
            'status' => 'success',
            'message' => 'Response default kehadiran',
            'data' => [
                'jml_kehadiran' => [
                    'jml_karyawan' => $jmlKaryawan,
                    'jml_masuk' => $jmlMasuk,
                    'jml_pulang' => $jmlPulang,
                    'jml_absen' => $jmlAbsen
                ],
                'list_absen' => [
                    'masuk' => $listMasuk,
                    'pulang' => $listPulang
                ]
            ]
        ]);

        return $response;
    }

    public function donloadKehadiran(Request $request)
    {
        $start_time = $request->start_time;
        $end_time = $request->end_time;

        return Excel::download(new AbsensiExport($start_time, $end_time), "datakehadiran|$start_time --> $end_time.xlsx");
//        return (new AbsensiExport($start_time, $end_time))->download("datakehadiran|bulan-$bulan.xlsx");
    }

    public function kehadiran(Request $request)
    {

        $search = $request->search;
        $startTime = $request->start_time;
        is_null($endTime = $request->end_time) ? $endTime = $startTime : $endTime = $request->end_time;
        $result_masuk = [];
        $result_pulang = [];


        // starttime dan search ada
        if (isset($startTime) && isset($search) ? true : false) {


            // search masuk
            $result_masuk = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'masuk')
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->get();

            // search pulang
            $result_pulang = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'pulang')
                ->whereBetween('tanggal_pulang', [$startTime, $endTime])
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'message' => 'history kehadiran',
                'data' => [
                    'jml_kehadiran' => [
                        'jml_karyawan' => [],
                        'jml_masuk' => [],
                        'jml_pulang' => [],
                        'jml_absen' => []
                    ],
                    'list_absen' => [
                        'masuk' => $result_masuk,
                        'pulang' => $result_pulang
                    ]
                ]
            ]);

        }

        // jika parameter start_time ada
        if (isset($startTime) ? true : false) {

            // Karyawan
            $jmlKaryawan = User::all()
                ->count();

            // Jumlah masuk
            $jmlMasuk = DB::table('absensis')
                ->where('keterangan', 'masuk')
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->count();
            // Jumlah pulang
            $jmlPulang = DB::table('absensis')
                ->where('keterangan', 'pulang')
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->count();
            $jmlAbsen = $jmlKaryawan - $jmlMasuk;

            // list absen masuk pulang dengan paginate
            $listMasuk = Absensi::with(['user'])
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->where('keterangan', 'masuk')
                ->orderBy('created_at', 'DESC')
                ->paginate(12);

            $listPulang = Absensi::with(['user'])
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->where('keterangan', 'pulang')
                ->orderBy('created_at', 'DESC')
                ->paginate(12);


            return response()->json([
                'message' => 'history kehadiran',
                'data' => [
                    'jml_kehadiran' => [
                        'jml_karyawan' => $jmlKaryawan,
                        'jml_masuk' => $jmlMasuk,
                        'jml_pulang' => $jmlPulang,
                        'jml_absen' => $jmlAbsen
                    ],
                    'list_absen' => [
                        'masuk' => $listMasuk,
                        'pulang' => $listPulang
                    ]
                ]
            ]);
        }


        // jika parameter search ada
        if (isset($search) ? true : false) {

            // Karyawan
            $jmlKaryawan = User::all()
                ->count();


            // Jumlah masuk
            $jmlMasuk = DB::table('absensis')
                ->where('keterangan', 'masuk')
                ->whereDate('created_at', Carbon::now())
                ->count();
            // Jumlah pulang
            $jmlPulang = DB::table('absensis')
                ->where('keterangan', 'pulang')
                ->whereDate('created_at', Carbon::now())
                ->count();
            $jmlAbsen = $jmlKaryawan - $jmlMasuk;

            // search masuk
            $result_masuk = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'masuk')
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->get();

            // search pulang
            $result_pulang = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'pulang')
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'message' => 'history kehadiran',
                'data' => [
                    'jml_kehadiran' => [
                        'jml_karyawan' => $jmlKaryawan,
                        'jml_masuk' => $jmlMasuk,
                        'jml_pulang' => $jmlPulang,
                        'jml_absen' => $jmlAbsen
                    ],
                    'list_absen' => [
                        'masuk' => $result_masuk,
                        'pulang' => $result_pulang
                    ]
                ]
            ]);
        }

        // kondisi default

        // Karyawan
        $jmlKaryawan = User::all()
            ->count();


        // Jumlah masuk
        $jmlMasuk = DB::table('absensis')
            ->where('keterangan', 'masuk')
            ->whereDate('created_at', Carbon::now())
            ->count();
        // Jumlah pulang
        $jmlPulang = DB::table('absensis')
            ->where('keterangan', 'pulang')
            ->whereDate('created_at', Carbon::now())
            ->count();
        $jmlAbsen = $jmlKaryawan - $jmlMasuk;


        // list absen masuk pulang dengan paginate
        $listMasuk = Absensi::with(['user'])
            ->whereDate('created_at', Carbon::now())
            ->where('keterangan', 'masuk')
            ->orderBy('created_at', 'DESC')
            ->paginate(12);

        $listPulang = Absensi::with(['user'])
            ->whereDate('created_at', Carbon::now())
            ->where('keterangan', 'pulang')
            ->orderBy('created_at', 'DESC')
            ->paginate(12);

        return response()->json([
            'message' => 'history kehadiran',
            'data' => [
                'jml_kehadiran' => [
                    'jml_karyawan' => $jmlKaryawan,
                    'jml_masuk' => $jmlMasuk,
                    'jml_pulang' => $jmlPulang,
                    'jml_absen' => $jmlAbsen
                ],
                'list_absen' => [
                    'masuk' => $listMasuk,
                    'pulang' => $listPulang
                ]
            ]
        ]);
    }

    public function detailAbsen($id)
    {
        $absen = Absensi::with(['user'])
            ->find($id);

        if (is_null($absen)){
            return response()->json([
                'message' => "Data Absen tidak ditemukan!"
            ], 404);
        }


        return response()->json([
            'status' => 200,
            'message' => "Detail Absen",
            'data' => [
                'absen' => new KehadiranResource($absen),
            ]
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $startTime = $request->start_time ? : Carbon::now()->format('Y-m-d');
        $endTime = $request->end_time ? : Carbon::now()->format('Y-m-d');

        $resultAbsen = Absensi::whereHas('user', function ($q) use($search) {
            $q->where('nama', 'like', '%'. $search .'%');
        })->whereDate('created_at', '>=', $startTime)
            ->whereDate('created_at', '<=', $endTime)
            ->with('user')->get();

        if (isset($resultAbsen)){
            return response()->json([
                'status' => 200,
                'message' => "Data Absen Karyawan $search",
                'data' => $resultAbsen
            ]);
        } else {
            return response()->json([
                'status' => 401,
                'message' => "Data Absen tidak ditemukan!"
            ], 401);
        }
    }

    public function kehadiranTerbaru()
    {
        $absen = Absensi::with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Kehadiran Terbaru',
            'data' => $absen
        ]);
    }
}
