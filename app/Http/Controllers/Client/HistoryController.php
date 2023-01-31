<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
            ->get();

        return response()->json([
            'messege' => 'success',
            'data' => $absensi
        ], 200);

    }

    public function default()
    {
        // data absensi berdasarkan id user dan tanggal sekarang
        $tanggal_sekarang = Carbon::now()->format('Y-m-d');
        $absensi = Absensi::where('user_id', Auth::user()->id)
            ->whereDate('tanggal_masuk', $tanggal_sekarang)
            ->get();

        return response()->json([
            'messege' => 'success',
            'data' => $absensi
        ], 200);

    }




}
