<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ProfileResource;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::where('id', Auth::user()->id)->first();
        $startMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        try {

            $kehadiran = Absensi::kehadiran()
                ->where('user_id', $user->id)
                ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
                ->count();

            $izin = Absensi::izin()
                ->where('user_id', $user->id)
                ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
                ->count();

            $absen = Absensi::absen()
                ->where('user_id', $user->id)
                ->whereBetween('tanggal_masuk', [$startMonth, $endMonth])
                ->count();
            $absen = $absen - $izin;


        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        if ($user->pf_foto) {
            $pf_foto = Storage::disk('public')->url($user->pf_foto);
        } else {
            $pf_foto = url('/storage/profile/userdefault.jpg');
        }

        return response()->json([
            'message' => 'success',
            'data' => [
                'user' => [
                    'nama' => $user->nama,
                    'niy' => $user->niy,
                    'email' => $user->email,
                    'pf_foto' => $pf_foto,
                ],
                'kehadiran' => [
                    'kehadiran' => $kehadiran,
                    'izin' => $izin,
                    'absen' => $absen,
                ]
            ]
        ], 200);
    }

    public function show()
    {
        $user = User::where('id', Auth::user()->id)
            ->with(['ktgkaryawan'])
            ->first();
        return response()->json([
            'message' => 'success',
            'data' => $user
        ], 200);
    }

    public function resetPassword(Request $request) {

        $request->validate([
            'pw_lama' => 'required',
            'pw_baru' => 'required',
        ]);

        try {
            if (!Hash::check($request->pw_lama, Auth::user()->password)) {
                return response()->json([
                    'message' => 'password lama tidak sesuai',
                ], 400);
            } else {
                    $user = User::where('id', Auth::user()->id)->first();
                    $user->password = Hash::make($request->pw_baru);
                    $user->save();
                    $user->tokens()->delete();
                    $token = $user->createToken('token-name')->plainTextToken;
                    return response()->json([
                        'message' => 'success',
                        'token' => $token
                    ], 200);
                }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error',
            ], 400);
        }
    }

    public function parseDate($datas)
    {
        $datas = $datas->toArray();
        $datas = collect($datas);
        $datas = $datas->sortBy('mulai_izin')->values()->all();

        $rekap = [];
        foreach ($datas as $data) {
            $start = Carbon::parse($data['mulai_izin']);
            $end = Carbon::parse($data['selesai_izin']);
            $data['tanggal'] = $start->format('Y-m-d');

            for ($date = $start; $date->lte($end); $date->addDay()) {
                $rekap[] = array_merge($data, ['tanggal' => $date->format('Y-m-d')]);
            }
        }

        return $rekap;
    }

}
