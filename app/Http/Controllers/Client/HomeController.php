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
use App\Models\HistoryNotif;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\Setting;
use App\Models\User;
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

        // cek apakah sekarang user sedang izin atau tidak
        $statusIzin = Absensi::statusIzin($request->user()->id);
        if ($statusIzin) {
            return response()->json([
                'message' => 'Anda sedang izin'
            ], 403);
        }

        // simpan foto
        $fotoPath = Absensi::simpanFoto($request->foto_masuk, 'foto_masuk');


        // jadwal dan batas absen
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        $jadwal = Absensi::batasAbsenMasuk($request->user()->id);

        // validasi klo ada jadwal absen hari ini
        if ($jadwal['jadwal_absen'] == false) {
            return response()->json([
                'message' => 'jadwal absen tidak ditemukan'
            ], 404);
        }

        // validasi waktu masuk
        $validasiMasuk = Absensi::validasiMasuk($jadwal, $now);

        if (!$validasiMasuk['status_waktu']) {
            return response()->json([
                'message' => 'belum waktunya absen masuk',
                'batas_absen' => $validasiMasuk['waktu_minimal_absen'],
            ], 400);
        }


        // validasi untuk kolom valid_masuk
        if ($request->is_valid_masuk == 1 && $validasiMasuk['validasi_waktu'] == 1) {
            $valid = 1;
        } else {
            $valid = 0;
        }

        try {
            $absen = Absensi::create([
                'user_id' => Auth::user()->id,
                'keterangan' => 'masuk',
                'valid_masuk' => $valid,
                'is_valid_masuk' => $request->is_valid_masuk,
                'isvld_wkt_masuk' => $validasiMasuk['validasi_waktu'],
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
            'message' => $validasiMasuk['message'],
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
        $fotoPath = Absensi::simpanFoto($request->foto_pulang, 'foto_pulang');

        // jadwal dan batas absen
        $now = Carbon::parse(Carbon::now()->format('H:i:s'));
        $jadwal = Absensi::batasAbsenPulang($request->user()->id);

        // validasi klo ada jadwal absen hari ini
        if ($jadwal['jadwal_absen'] == false) {
            return response()->json([
                'message' => 'jadwal absen tidak ditemukan'
            ], 404);
        }

        // validasi waktu pulang
        $validasiPulang = Absensi::validasiPulang($jadwal, $now);

        if ($request->is_valid_pulang == 1 && $validasiPulang['validasi_waktu'] == 1) {
            $valid = 1;
        } else {
            $valid = 0;
        }

        try {
            $absen = Absensi::where('user_id', Auth::user()->id)
                ->where('keterangan', 'masuk')
                ->where('id', $id)
                ->update([
                    'keterangan' => 'pulang',
                    'valid_pulang' => $valid,
                    'is_valid_pulang' => $request->is_valid_pulang,
                    'isvld_wkt_pulang' => $validasiPulang['validasi_waktu'],
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
            'message' => $validasiPulang['message'],
            'data' => $absen,
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
                'file_name' => $request->file_izin->getClientOriginalName() ?? null,
                'path_file' => $filePath,
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        // Instance DashboardController
//        $dashboard = new DashboardController();
//        $jmlKehadiran = $dashboard->dashboard();
//        $jmlKehadiran = $jmlKehadiran->original;
//        $jmlKehadiran = $jmlKehadiran['data'];
//        event(new JmlKehadiranDashboardEvent($jmlKehadiran));

        // Instance KehadiranController
//        $kehadiran = new KehadiranController();
//        $jmlkehadiran = $kehadiran->jmlKehadiran($request);
//        $jmlkehadiran = $jmlkehadiran->original;
//        $jmlkehadiran = $jmlkehadiran['data'];
//        event(new JmlKehadiranEvent($jmlkehadiran));

//        event(new IzinEvent($izin->id));

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
            ->where('keterangan', 'pulang')
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
        $jadwalAbsen ? $jadwalMasuk = $jadwalAbsen->jam_masuk : $jadwalMasuk = null;
        $jadwalAbsen ? $jadwalPulang = $jadwalAbsen->jam_pulang : $jadwalPulang = null;

        // kondisi jika tidak ada jadwal absen
        if ($jadwalMasuk == null && $jadwalPulang == null) {
            $jadwalMasuk = 'Tidak ada Jadwal';
            $jadwalPulang = 'Tidak ada Jadwal';
        } else {
            $jadwalMasuk = Carbon::parse($jadwalMasuk)->format('H.i');
            $jadwalPulang = Carbon::parse($jadwalPulang)->format('H.i');
        }

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
                'masuk' => $jadwalMasuk,
                'pulang' => $jadwalPulang,
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

    public function historyNotif()
    {
        $data = User::find(Auth::user()->id)
            ->historyNotif()
            ->orderBy('created_at', 'desc')
            ->get();

        $dataMapped = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => "Pemberitahuan Acara",
                'message' => "Hari ini anda punya acara $item->judul",
                'type' => $item->kategori_event,
                'created_at' => $item->created_at->format('d-m-Y H:i:s')
            ];
        });

        return response()->json([
            'message' => 'history notif',
            'data' => $dataMapped ?? ''
        ]);
    }

    public function readNotif($notif_id)
    {
        $notif = $notif_id;

        if (!$notif) {
            return response()->json([
                'message' => 'notif id belum di input',
                'data' => ''
            ], 400);
        }

        foreach ($notif as $ntf) {
            HistoryNotif::where('id', $ntf)
                ->update([
                    'read' => true
                ]);
        }

        return response()->json([
            'message' => 'success'
        ]);
    }
}
