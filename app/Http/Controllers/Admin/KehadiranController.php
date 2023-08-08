<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class KehadiranController extends Controller
{

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
                ->with(['user'])
                ->orderBy('created_at', 'DESC')
                ->get();

            // search pulang
            $result_pulang = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'pulang')
                ->whereBetween('tanggal_pulang', [$startTime, $endTime])
                ->with(['user'])
                ->orderBy('created_at', 'DESC')
                ->get();

            // search izin
            $result_izin = Izin::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->whereDate('selesai_izin', '>=', $startTime)
                ->whereDate('mulai_izin', '<=', $endTime)
                ->with(['user'])
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'message' => 'history kehadiran',
                'data' => [
                    'masuk' => $result_masuk,
                    'pulang' => $result_pulang,
                    'izin' => $result_izin
                ]
            ]);

        }

        // jika parameter start_time ada
        if (isset($startTime) ? true : false) {

            // list absen masuk pulang dengan paginate
            $listMasuk = Absensi::with(['user'])
                ->where('keterangan', 'masuk')
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->orderBy('created_at', 'DESC')
                ->get();

            $listPulang = Absensi::with(['user'])
                ->where('keterangan', 'pulang')
                ->whereBetween('tanggal_pulang', [$startTime, $endTime])
                ->orderBy('created_at', 'DESC')
                ->get();

            $listIzin = Izin::with(['user'])
                ->whereDate('selesai_izin', '>=', $startTime)
                ->whereDate('mulai_izin', '<=', $endTime)
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'message' => 'history kehadiran',
                'data' => [
                    'masuk' => $listMasuk,
                    'pulang' => $listPulang,
                    'izin' => $listIzin
                ]
            ]);
        }

        // jika parameter search ada
        if (isset($search) ? true : false) {
            // search masuk
            $result_masuk = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'masuk')
                ->whereDate('tanggal_masuk', Carbon::now())
                ->with(['user'])
                ->orderBy('created_at', 'DESC')
                ->get();

            // search pulang
            $result_pulang = Absensi::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->where('keterangan', 'pulang')
                ->whereDate('tanggal_masuk', Carbon::now())
                ->with(['user'])
                ->orderBy('created_at', 'DESC')
                ->get();

            // search izin
            $result_izin = Izin::whereHas('user', function ($q) use($search) {
                $q->where('nama', 'like', '%'. $search .'%');
            })->whereDate('mulai_izin', '<=', Carbon::now())
                ->whereDate('selesai_izin', '>=', Carbon::now())
                ->with(['user'])
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'message' => 'history kehadiran',
                'data' => [
                    'masuk' => $result_masuk,
                    'pulang' => $result_pulang,
                    'izin' => $result_izin
                ]
            ]);
        }

        // kondisi default
        // list absen masuk pulang dengan paginate
        $listMasuk = Absensi::with(['user'])
            ->where('keterangan', 'masuk')
            ->whereDate('tanggal_masuk', Carbon::now())
            ->orderBy('created_at', 'DESC')
            ->get();

        $listPulang = Absensi::with(['user'])
            ->where('keterangan', 'pulang')
            ->whereDate('tanggal_pulang', Carbon::now())
            ->orderBy('created_at', 'DESC')
            ->get();

        $listIzin = Izin::with(['user'])
            ->whereDate('mulai_izin', '<=', Carbon::now())
            ->whereDate('selesai_izin', '>=', Carbon::now())
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json([
            'message' => 'history kehadiran',
            'data' => [
                'masuk' => $listMasuk,
                'pulang' => $listPulang,
                'izin' => $listIzin
            ]
        ]);
    }

    public function jmlKehadiran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'date_format:Y-m-d',
            'end_time' => 'date_format:Y-m-d'
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        $data['jumlah_karyawan'] = Jadwal::jumlahKaryawan($request->start_time);
        $data['tanggal'] = Carbon::now()->format('Y-m-d');

        if ($request->start_time && $request->end_time) {
            $startTime = $request->start_time;
            $endTime = $request->end_time;

            $data['jumlah_kehadiran'] = Absensi::kehadiran()
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->count();

            // menetapkan range
            $data['jumlah_izin'] = Absensi::izin()
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->count();

            $data['jumlah_absen'] = Absensi::absen()
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->count();

        } else if ($request->start_time) {
            $tanggal = $request->start_time;

            $data['jumlah_kehadiran'] = Absensi::kehadiran()
                ->where('tanggal_masuk', $tanggal)
                ->count();

            $data['jumlah_izin'] = Absensi::izin()
                ->where('tanggal_masuk', $tanggal)
                ->count();

            $data['jumlah_absen'] = Absensi::absen()
                ->where('tanggal_masuk', $tanggal)
                ->count();
            $data['jumlah_absen'] = $data['jumlah_absen'] - $data['jumlah_izin'];

        } else {
            $tanggal = Carbon::now()->format('Y-m-d');

            $data['jumlah_kehadiran'] = Absensi::kehadiran()
                ->where('tanggal_masuk', $tanggal)
                ->count();

            $data['jumlah_izin'] = Absensi::izin()
                ->where('tanggal_masuk', $tanggal)
                ->count();

            $data['jumlah_absen'] = Absensi::absen()
                ->where('tanggal_masuk', $tanggal)
                ->count();
            $data['jumlah_absen'] = $data['jumlah_absen'] - $data['jumlah_izin'];

        }

        return response()->json([
            'message' => 'jumlah kehadiran',
            'data' => $data
        ]);
    }

    public function testKehadiran(Request $request)
    {
        $jmlKaryawan = User::all()
            ->count();
        $jmlKehadiran = 0;
        $jmlIzin = 0;
        $jmlAbsen = 0;

        if ($request->end_time) {
            $startTime = $request->start_time;
            $endTime = $request->end_time;

            $jmlKehadiran = Absensi::where('is_valid_pulang', '1')
                ->whereBetween('tanggal_pulang', [$startTime, $endTime])
                ->count();
            // menetapkan range
            $jmlIzin = DB::table('izins')
                ->whereDate('selesai_izin', '>=', $startTime)
                ->whereDate('mulai_izin', '<=', $endTime)
                ->count();
            $jmlAbsen = Absensi::where('is_valid_masuk', '0')
                ->whereBetween('tanggal_masuk', [$startTime, $endTime])
                ->count();

        } else if ($request->start_time) {
            $startTime = $request->start_time;
            $jmlKehadiran = Absensi::where('is_valid_pulang', '1')
                ->where('tanggal_pulang', $startTime)
                ->count();
            $jmlIzin = DB::table('izins')
                ->whereDate('mulai_izin', '<=', $startTime)
                ->whereDate('selesai_izin', '>=', $startTime)
                ->count();
            $jmlAbsen = Absensi::where('is_valid_masuk', '0')
                ->where('tanggal_masuk', $startTime)
                ->count();

        } else {
            $startTime = Carbon::now()->format('Y-m-d');

            $jmlKehadiran = Absensi::where('is_valid_pulang', '1')
                ->where('tanggal_pulang', $startTime)
                ->count();
            $jmlIzin = Izin::whereDate('mulai_izin', '<=', $startTime)
                ->whereDate('selesai_izin', '>=', $startTime)
                ->count();
            $jmlAbsen = Absensi::where('is_valid_masuk', '0')
                ->where('tanggal_masuk', $startTime)
                ->count();
        }

        return response()->json([
            'jml_karyawan' => $jmlKaryawan,
            'jml_kehadiran' => $jmlKehadiran,
            'jml_izin' => $jmlIzin,
            'jml_absen' => $jmlAbsen
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
            'data' => $absen
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

    public function detailIzin($id)
    {
        $izin = Izin::with(['user'])
            ->find($id);

        $user = User::find($izin->user_id);

        if (is_null($izin)){
            return response()->json([
                'message' => "Data Izin tidak ditemukan!"
            ], 404);
        }

        return response()->json([
            'message' => "success",
            'data' => $izin,
            'user' => $user
        ]);
    }
}
