<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\KehadiranResource;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isNull;

class KehadiranController extends Controller
{
    public function kehadiran(){
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

    public function historyKehadiran(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'tanggal' => ['required', 'integer'],
           'bulan' => ['required', 'integer'],
           'tahun' => ['required', 'integer'],
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $tanggal = $request->tanggal;
        $bulan = $request->bulan;
        $tahun = $request->tahun;


        // list absen masuk pulang dengan paginate
        $listMasuk = Absensi::with(['user'])
            ->whereDate('created_at', "$tahun-$bulan-$tanggal")
            ->where('keterangan', 'masuk')
            ->paginate(12);

        $listPulang = Absensi::with(['user'])
            ->whereDate('created_at', "$tahun-$bulan-$tanggal")
            ->where('keterangan', 'pulang')
            ->paginate(12);

        return response()->json([
            'message' => 'history kehadiran',
            'data' => [
                'masuk' => $listMasuk,
                'pulang' => $listPulang
            ]
        ]);
    }

    public function detailAbsen($id)
    {
        $absen = Absensi::with(['user'])
            ->find($id);

        return new KehadiranResource($absen);
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
}
