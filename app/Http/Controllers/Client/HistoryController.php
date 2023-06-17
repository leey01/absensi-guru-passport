<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\IzinResource;
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

        return response()->json([
            'messege' => 'success',
            'data' => [
                'absen' => $absensi,
                'izin' => $izin
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
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        $startTime = $request->start_time;
        $endTime = $request->end_time;

        try {
            $absen = Absensi::where('user_id', Auth::user()->id)
                ->whereDate('tanggal_masuk', '>=', $startTime)
                ->whereDate('tanggal_masuk', '<=', $endTime)
                ->orderBy('tanggal_masuk', 'asc')
                ->get()
                ->groupBy(function($date) {
                    return Carbon::parse($date->tanggal_masuk)->format('Y-m-d');
                });

            $izin = Izin::where('user_id', Auth::user()->id)
                ->whereDate('selesai_izin', '>=', $startTime)
                ->whereDate('mulai_izin', '<=', $endTime)
                ->orderBy('mulai_izin', 'asc')
                ->get();

            $izin = $this->parseDate($izin);

            $izin = collect($izin)->groupBy('tanggal')->toArray();


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
            ],
        ], 200);

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

            for ($i = $start; $i <= $end; $i->addDay()) {
                $rekap[] = array_merge($data, ['tanggal' => Carbon::parse($i)->format('Y-m-d')]);
            }
        }

        return $rekap ?? null;
    }

}
