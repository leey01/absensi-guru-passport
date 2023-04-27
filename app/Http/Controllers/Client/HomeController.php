<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function absenMasuk(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'is_valid_masuk' => 'required|int|in:0,1',
           'foto_masuk' => 'required',
           'lokasi_masuk' => 'required',
           'longitude_masuk' => 'required',
           'latitude_masuk' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        // Foto Masuk
        $timestamp = time();
        $fotoName = $timestamp . $request->foto_masuk->getClientOriginalName();
        $fotoPath = 'foto_masuk/'. $fotoName;
        Storage::disk('public')->put($fotoPath, file_get_contents($request->foto_masuk));

        try {
            $absen = Absensi::create([
                'user_id' => Auth::user()->id,
                'keterangan' => 'masuk',
                'is_valid_masuk' => $request->is_valid_masuk,
                'catatan_masuk' => $request->catatan_masuk,
                'waktu_masuk' => $request->waktu_masuk,
                'tanggal_masuk' => $request->tanggal_masuk,
                'foto_masuk' => $fotoPath,
                'lokasi_masuk' => $request->lokasi_masuk,
                'longitude_masuk' => $request->longitude_masuk,
                'latitude_masuk' => $request->latitude_masuk
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed create data",
                'data' => $e
            ], 401);
        }

        return response()->json([
            'message' => 'absen masuk berhasil',
            'data' => $absen
        ]);
    }

    public function absenPulang(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'is_valid_pulang' => 'required|int|in:0,1',
            'foto_pulang' => 'required',
            'lokasi_pulang' => 'required',
            'longitude_pulang' => 'required',
            'latitude_pulang' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        // Foto Pulang
        $timestamp = time();
        $fotoName = $timestamp . $request->foto_pulang->getClientOriginalName();
        $fotoPath = 'foto_pulang/'. $fotoName;
        Storage::disk('public')->put($fotoPath, file_get_contents($request->foto_pulang));
        $url = Storage::disk('public')->url($fotoPath);

        try {
            $absen = Absensi::where('user_id', Auth::user()->id)
                ->where('keterangan', 'masuk')
                ->where('id', $id);

            $absen->update([
                'keterangan' => 'pulang',
                'is_valid_pulang' => $request->is_valid_pulang,
                'catatan_pulang' => $request->catatan_pulang,
                'waktu_pulang' => $request->waktu_pulang,
                'tanggal_pulang' => $request->tanggal_pulang,
                'foto_pulang' => $fotoPath,
                'lokasi_pulang' => $request->lokasi_pulang,
                'longitude_pulang' => $request->longitude_pulang,
                'latitude_pulang' => $request->latitude_pulang,
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed update data",
                'data' => $e
            ], 401);
        }

        return response()->json([
            'message' => 'absen pulang berhasil',
        ]);
    }

    public function izin(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'jenis_izin' => 'required',
            'mulai_izin' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        // File Bukti Izin
        $timestamp = time();
        $fileName = $timestamp . $request->file_izin->getClientOriginalName();
        $filePath = 'file_izin/'. $fileName;
        Storage::disk('public')->put($filePath, file_get_contents($request->file_izin));

        try {
            $izin = Izin::create([
                'user_id' => Auth::user()->id,
                'jenis_izin' => $request->jenis_izin,
                'mulai_izin' => $request->mulai_izin,
                'selesai_izin' => $request->selesai_izin,
                'deskripsi' => $request->deskripsi,
                'path_file' => $filePath,
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'izin berhasil',
            'data' => $izin
        ]);
    }

    public function kehadiran(Request $request)
    {
        // user
        $user = DB::table('users')
            ->find(Auth::user()->id);

        // data absen masuk
        $dataAbsenMasuk = Absensi::where('user_id', Auth::user()->id)
            ->whereDate('created_at', '=', Carbon::today()->toDateString())
            ->whereNotNull('tanggal_masuk')
            ->first();
        // waktu masuk
        $dataAbsenMasuk ? $waktuMasuk = $dataAbsenMasuk->waktu_masuk : $waktuMasuk = '';
        // status masuk
        $dataAbsenMasuk ? $statusMasuk = true : $statusMasuk = false;

        // data absen pulang
        $dataAbsenPulang = Absensi::where('user_id', Auth::user()->id)
            ->whereDate('created_at', '=', Carbon::today()->toDateString())
            ->whereNotNull('tanggal_pulang')
            ->first();
        // waktu pulang
        $dataAbsenPulang ? $waktuPulang = $dataAbsenPulang->waktu_pulang : $waktuPulang = '';
        // status pulang
        $dataAbsenPulang ? $statusPulang = true : $statusPulang = false;

        // jadwal absen
        $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
            ->where('hari', $request->hari)
            ->first();
        $jadwalAbsen ? $jadwalMasuk = $jadwalAbsen->jam_masuk : $jadwalMasuk = '';
        $jadwalAbsen ? $jadwalPulang = $jadwalAbsen->jam_pulang : $jadwalPulang = '';

        return response()->json([
            'user' => [
                'name' => $user->nama,
                'email' => $user->email,
            ],
            'jadwal_absen' => [
                'masuk' => $jadwalMasuk,
                'pulang' => $jadwalPulang,
            ],
            'status_absen' => [
                'masuk' => $statusMasuk,
                'pulang' => $statusPulang,
            ],
            'waktu_absen' => [
                'masuk' => $waktuMasuk ? $waktuMasuk : '',
                'pulang' => $waktuPulang ? $waktuPulang : '',
            ]
        ]);
    }

    public function jadwalAbsen(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'hari' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
                ->where('hari', $request->hari)
                ->first();

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        if (!$jadwalAbsen) {
            return response()->json([
                'message' => 'tidak ada jadwal absen untuk hari ini',
                'data' => ''
            ], 404);
        }

        return response()->json([
            'message' => 'jadwal absen',
            'data' => $jadwalAbsen
        ]);
    }
}
