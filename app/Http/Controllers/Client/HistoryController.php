<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\KehadiranResource;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */

    public function index(Request $request)
    {
        // data absensi berdasarkan id user dan tanggal yg diinputkan user
        $absensi = Absensi::where('user_id', Auth::user()->id)
            ->whereDate('tanggal_masuk', $request->tanggal)
            ->first();

        if ($absensi == null) {
            return response()->json([
                'messege' => 'data tidak ditemukan',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'messege' => 'success',
            'data' => new KehadiranResource($absensi)
        ], 200);

    }

    public function default()
    {
        // data absensi berdasarkan id user dan tanggal sekarang
        $tanggal_sekarang = Carbon::now()->format('Y-m-d');
        $absensi = Absensi::where('user_id', Auth::user()->id)
            ->whereDate('tanggal_masuk', $tanggal_sekarang)
            ->first();

        if ($absensi == null) {
            return response()->json([
                'messege' => 'data tidak ditemukan',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'messege' => 'success',
            'data' => new KehadiranResource($absensi)
        ], 200);

    }




}
