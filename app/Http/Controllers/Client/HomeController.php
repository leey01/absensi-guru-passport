<?php

namespace App\Http\Controllers\Client;

use App\Events\IzinEvent;
use App\Events\JmlKehadiranDashboardEvent;
use App\Events\JmlKehadiranEvent;
use App\Events\KehadiranMasukEvent;
use App\Events\KehadiranPulangEvent;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KehadiranController;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


        // waktu sekarang
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        //batas mulai absen masuk
        $time = Setting::where('key', 'batas_waktu_absen_masuk')->first();
        $time = explode(':', $time->value);

        // jadwal masuk user
        $hari = Carbon::now()->format('Y-m-d');
        $hari = Carbon::parse($hari)->locale('id');
        $hari->settings(['formatFunction' => 'translatedFormat']);
        $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
            ->where('hari', $hari->format('l'))
            ->first();
        if (!$jadwalAbsen) {
            return response()->json([
                'message' => 'jadwal absen tidak ditemukan'
            ], 404);
        }
        $jadwalAbsen = Carbon::parse($jadwalAbsen->jam_masuk);

        // waktu minimal user bisa absen
        $modifiedTime = $jadwalAbsen->copy()->subHours($time[0])->subMinutes($time[1])->subSeconds($time[2]);

        // jika waktu minimal absen masuk berkurang melebihi tengah malam akan jadi 00:00
        if ($modifiedTime->isYesterday()) {
            $batasAbsen = Carbon::now()->startOfDay();
        } else {
            $batasAbsen = $modifiedTime;
        }

        // validasi waktu masuk
        // validasi klo ada jadwal absen hari ini
        if ($jadwalAbsen) {
            // jika waktu sekarang kurang dari batas waktu masuk
            if ($now->lessThan($batasAbsen)) {
                return response()->json([
                    'message' => 'belum waktunya absen masuk',
                    'waktu_minimal_absen' => $batasAbsen->format('H:i:s'),
                ], 400);
            } else {
                // jika waktu sekarang kurang dari jadwal masuk maka akan true
                if ($now->lessThan($jadwalAbsen)) {
                    $isValid = 1;
                    $message = 'absen masuk berhasil';
                } else {
                    $isValid = 0;
                    $message = 'absen masuk terlambat';
                }
            }
        }

        // validasi untuk kolom valid_masuk
        $request->is_valid_masuk == 1 && $isValid == 1 ? $valid = 1 : $valid = 0;


        try {
            $absen = Absensi::create([
                'user_id' => Auth::user()->id,
                'keterangan' => 'masuk',
                'valid_masuk' => $valid,
                'is_valid_masuk' => $request->is_valid_masuk,
                'isvld_wkt_masuk' => $isValid,
                'catatan_masuk' => $request->catatan_masuk,
                'waktu_masuk' => Carbon::now()->format('H:i:s'),
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
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

//        // Instance DashboardController
//        $dashboard = new DashboardController();
//        $jmlKehadiran = $dashboard->dashboard();
//        $jmlKehadiran = $jmlKehadiran->original;
//        $jmlKehadiran = $jmlKehadiran['data'];
//        event(new JmlKehadiranDashboardEvent($jmlKehadiran));
//
//        // Instance KehadiranController
//        $kehadiran = new KehadiranController();
//        $jmlkehadiran = $kehadiran->jmlKehadiran($request);
//        $jmlkehadiran = $jmlkehadiran->original;
//        $jmlkehadiran = $jmlkehadiran['data'];
//        event(new JmlKehadiranEvent($jmlkehadiran));
//
//        // dispatch event kehadiran
//        event(new KehadiranMasukEvent($absen->id));

        return response()->json([
            'message' => $message,
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

        // validasi waktu absen pulang
        // waktu sekarang
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        //batas mulai absen masuk
        $time = Setting::where('key', 'batas_waktu_absen_pulang')->first();
        $time = explode(':', $time->value);

        // jadwal pulang user
        $hari = Carbon::now()->format('Y-m-d');
        $hari = Carbon::parse($hari)->locale('id');
        $hari->settings(['formatFunction' => 'translatedFormat']);
        $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
            ->where('hari', $hari->format('l'))
            ->first();
        $jadwalAbsen = Carbon::parse($jadwalAbsen->jam_pulang);

        // waktu maksimal user bisa absen
        $modifiedTime = $jadwalAbsen->copy()->addHours($time[0])->addMinutes($time[1])->addSeconds($time[2]);

        // jika waktu maksimal absen pulang bertambah melebihi tengah malam akan jadi 23:59
        if ($modifiedTime->isTomorrow()) {
            $batasAbsen = Carbon::now()->endOfDay();
        } else {
            $batasAbsen = $modifiedTime;
        }

        // validasi waktu pulang
        // validasi klo ada jadwal absen hari ini
        if ($jadwalAbsen) {
            // jika waktu sekarang melebihi dari batas waktu pulang
            if ($now->greaterThan($batasAbsen)) {
                $message = 'waktu absen pulang sudah selesai';
                $isValid = 0;
            } else {
                // jika waktu sekarang melebihi dari jadwal pulang maka akan true
                if ($now->greaterThan($jadwalAbsen)) {
                    $isValid = 1;
                    $message = 'absen pulang berhasil';
                } else {
                    $isValid = 0;
                    $message = 'waktu absen pulang belum tiba';
                }
            }
        } else {
            return response()->json([
                'message' => 'jadwal tidak ditemukan, tidak perlu absen'
            ], 404);
        }

        $request->is_valid_pulang == 1 && $isValid == 1 ? $valid = 1 : $valid = 0;

        try {
            $absen = Absensi::where('user_id', Auth::user()->id)
                ->where('keterangan', 'masuk')
                ->where('id', $id)
                ->update([
                    'keterangan' => 'pulang',
                    'valid_pulang' => $valid,
                    'is_valid_pulang' => $request->is_valid_pulang,
                    'isvld_wkt_pulang' => $isValid,
                    'catatan_pulang' => $request->catatan_pulang,
                    'waktu_pulang' => Carbon::now()->format('H:i:s'),
                    'tanggal_pulang' => Carbon::now()->format('Y-m-d'),
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

//        // Instance DashboardController
//        $dashboard = new DashboardController();
//        $jmlKehadiran = $dashboard->dashboard();
//        $jmlKehadiran = $jmlKehadiran->original;
//        $jmlKehadiran = $jmlKehadiran['data'];
//        event(new JmlKehadiranDashboardEvent($jmlKehadiran));
//
//        // Instance KehadiranController
//        $kehadiran = new KehadiranController();
//        $jmlkehadiran = $kehadiran->jmlKehadiran($request);
//        $jmlkehadiran = $jmlkehadiran->original;
//        $jmlkehadiran = $jmlkehadiran['data'];
//        event(new JmlKehadiranEvent($jmlkehadiran));
//
//        // dispatch event kehadiran
//        event(new KehadiranPulangEvent($id));

        $absen = Absensi::find($id);
        return response()->json([
            'message' => $message,
            'data' => $absen,
            'batas_absen' => $batasAbsen->format('H:i:s'),
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

        // Instance DashboardController
        $dashboard = new DashboardController();
        $jmlKehadiran = $dashboard->dashboard();
        $jmlKehadiran = $jmlKehadiran->original;
        $jmlKehadiran = $jmlKehadiran['data'];
        event(new JmlKehadiranDashboardEvent($jmlKehadiran));

        // Instance KehadiranController
        $kehadiran = new KehadiranController();
        $jmlkehadiran = $kehadiran->jmlKehadiran($request);
        $jmlkehadiran = $jmlkehadiran->original;
        $jmlkehadiran = $jmlkehadiran['data'];
        event(new JmlKehadiranEvent($jmlkehadiran));

        event(new IzinEvent($izin->id));

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
        $dataAbsenMasuk ? $tanggalMasuk = $dataAbsenMasuk->tanggal_masuk : $tanggalMasuk = '';
        // status masuk
        $dataAbsenMasuk ? $statusMasuk = true : $statusMasuk = false;

        // data absen pulang
        $dataAbsenPulang = Absensi::where('user_id', Auth::user()->id)
            ->whereDate('created_at', '=', Carbon::today()->toDateString())
            ->whereNotNull('tanggal_pulang')
            ->first();
        // waktu pulang
        $dataAbsenPulang ? $waktuPulang = $dataAbsenPulang->waktu_pulang : $waktuPulang = '';
        $dataAbsenPulang ? $tanggalPulang = $dataAbsenPulang->tanggal_pulang : $tanggalPulang = '';
        // status pulang
        $dataAbsenPulang ? $statusPulang = true : $statusPulang = false;

        // jadwal absen
        $jadwalAbsen = Jadwal::where('user_id', Auth::user()->id)
            ->where('hari', $request->hari)
            ->first();
        $jadwalAbsen ? $jadwalMasuk = $jadwalAbsen->jam_masuk : $jadwalMasuk = '';
        $jadwalAbsen ? $jadwalPulang = $jadwalAbsen->jam_pulang : $jadwalPulang = '';

        // kordinat
        $kor = Setting::whereIn("key", ["longitude", "latitude", "radius"])
            ->get()
            ->groupBy("key");

        foreach ($kor as $key => $value) {
            $kor[$key] = $value[0]->value ?? '';
        }

        // add id absen
        $kor['id_absen'] = $dataAbsenMasuk->id ?? 0;

        return response()->json([
            'user' => [
                'name' => $user->nama,
                'email' => $user->email,
            ],
            'jadwal_absen' => [
                'masuk' => Carbon::parse($jadwalMasuk)->format('H.i'),
                'pulang' => Carbon::parse($jadwalPulang)->format('H.i'),
            ],
            'status_absen' => [
                'masuk' => $statusMasuk,
                'pulang' => $statusPulang,
            ],
            'waktu_absen' => [
                'masuk' =>  "$tanggalMasuk $waktuMasuk" ?? '',
                'pulang' => "$tanggalPulang $waktuPulang" ?? '',
            ],
            'absen' => $kor
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
