<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\KehadiranResource;
use App\Models\Izin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
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

    public function absen(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'tanggal' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            // data absensi berdasarkan id user dan tanggal yg diinputkan user
            $absensi = Absensi::where('user_id', Auth::user()->id)
                ->whereDate('tanggal_masuk', $request->tanggal)
                ->first();

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'messege' => 'success',
            'data' => [
                'absen' => new KehadiranResource($absensi),
            ]
        ], 200);

    }

    public function izin(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'tanggal' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            // data absensi berdasarkan id user dan tanggal yg diinputkan user
            $izin = Izin::where('user_id', Auth::user()->id)
                ->whereDate('mulai_izin', '<=', $request->tanggal)
                ->whereDate('selesai_izin', '>=', $request->tanggal)
                ->first();
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        $izin->path_file = Storage::disk('public')->url($izin->path_file);

        return response()->json([
            'messege' => 'success',
            'data' => $izin
        ], 200);

    }

    public function recap(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'bulan' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        $bulan = $request->bulan;

        try {
            $absen = Absensi::where('user_id', Auth::user()->id)
                ->whereMonth('tanggal_masuk', $bulan)
                ->get();

            $izin = Izin::where('user_id', Auth::user()->id)
                ->whereMonth('mulai_izin', $bulan)
                ->get();

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => "success",
            'data' => [
                'absen' => $absen,
                'izin' => $izin
            ]
        ], 200);

    }




}
