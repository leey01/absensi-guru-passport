<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
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
           'foto_masuk' => 'required',
           'lokasi_masuk' => 'required',
           'longitude_masuk' => 'required',
           'latitude_masuk' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        // Foto Masuk
        $timestamp = time();
        $fotoName = $timestamp . $request->foto_masuk->getClientOriginalName();
        $fotoPath = 'foto_masuk/'. $fotoName;
        Storage::disk('public')->put($fotoPath, file_get_contents($request->foto_masuk));
        $url = Storage::disk('public')->url($fotoPath);

        try {
            $absen = Absensi::create([
                'user_id' => Auth::user()->id,
                'keterangan' => 'masuk',
                'catatan_masuk' => $request->catatan_masuk,
                'waktu_masuk' => Carbon::now()->format('h:i:s'),
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'foto_masuk' => $fotoPath,
                'lokasi_masuk' => $request->lokasi_masuk,
                'longitude_masuk' => $request->longitude_masuk,
                'latitude_masuk' => $request->latitude_masuk
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed " . $e
            ], 401);
        }

        return response()->json([
            'message' => 'absen masuk berhasil',
            'data' => $absen
        ]);
    }

    public function absenPulang(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'foto_pulang' => 'required',
            'lokasi_pulang' => 'required',
            'longitude_pulang' => 'required',
            'latitude_pulang' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
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
                ->where('tanggal_masuk', Carbon::now()->format('Y-m-d'));

            $absen->update([
                'keterangan' => 'pulang',
                'catatan_pulang' => $request->catatan_pulang,
                'waktu_pulang' => Carbon::now()->format('h:i:s'),
                'tanggal_pulang' => Carbon::now()->format('Y-m-d'),
                'foto_pulang' => $fotoPath,
                'lokasi_pulang' => $request->lokasi_pulang,
                'longitude_pulang' => $request->longitude_pulang,
                'latitude_pulang' => $request->latitude_pulang,
            ]);
//            $absen->keterangan = 'pulang';
//            $absen->catatan_pulang = $request->catatan_pulang;
//            $absen->waktu_pulang = Carbon::now()->format('h:i:s');
//            $absen->tanggal_pulang = Carbon::now()->format('Y-m-d');
//            $absen->foto_pulang = $request->foto_pulang;
//            $absen->lokasi_pulang = $request->lokasi_pulang;
//            $absen->longitude_keluar = $request->longitude_keluar;
//            $absen->latitude_keluar = $request->latitude_keluar;
//            $absen->save();

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed " . $e
            ], 401);
        }

        return response()->json([
            'message' => 'absen keluar berhasil',
        ]);
    }

    public function kehadiran()
    {
        $masuk = false;
        $pulang = false;

        $absenMasuk = Absensi::where('user_id', Auth::user()->id)
            ->where('keterangan', 'masuk')
            ->whereDate('created_at', '=', Carbon::today()->toDateString())
            ->exists();

        $absenPulang = Absensi::where('user_id', Auth::user()->id)
            ->where('keterangan', 'pulang')
            ->whereDate('created_at', '=', Carbon::today()->toDateString())
            ->exists();

        // nama user
        $user = DB::table('users')
            ->find(Auth::user()->id);

        if ($absenMasuk) {
            $masuk = true;
        } elseif ($absenPulang) {
            $pulang = true;
        }

        $status = [
            'masuk' => $masuk,
            'pulang' => $pulang
        ];


        return response()->json([
            'user' => $user,
            'status_absen' => $status,
        ]);
    }
}
