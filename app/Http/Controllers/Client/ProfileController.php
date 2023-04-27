<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ProfileResource;
use App\Models\Absensi;
use App\Models\Izin;
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

        try {
            $masuk = Absensi::where('user_id', Auth::user()->id)
                ->where('keterangan', 'masuk')
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count();

            $pulang = Absensi::where('user_id', Auth::user()->id)
                ->where('keterangan', 'pulang')
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count();

            $izin = Izin::where('user_id', Auth::user()->id)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count();

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
            ->with(['ktgkaryawan', 'isAdmin'])
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
