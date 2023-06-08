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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::where('id', Auth::user()->id)->first();
        $jmlJadwal = Jadwal::where('user_id', Auth::user()->id)
            ->where('')
            ->count();
        $tanggal = Carbon::now()->format('Y-m-d');
        $startMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now()->format('Y-m-d');

        try {
            $kehadiran = Absensi::where('user_id', Auth::user()->id)
                ->where('is_valid_masuk', '1')
                ->where('isvld_wkt_masuk', '1')
                ->where('is_valid_pulang', '1')
                ->where('isvld_wkt_pulang', '1')
                ->where('tanggal_pulang', $tanggal)
                ->count();

            $izin = Izin::where('user_id', Auth::user()->id)
                ->whereDate('mulai_izin', '<=', $tanggal)
                ->whereDate('selesai_izin', '>=', $tanggal)
                ->count();

            $absen = [$startMonth, $today];


        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        if ($user->pf_foto) {
            $pf_foto = Storage::disk('public')->url($user->pf_foto);
        } else {
            $pf_foto = url('/storage/profile/userdefault.png');
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
                    'masuk' => $masuk,
                    'pulang' => $pulang,
                    'izin' => $izin,
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

}
