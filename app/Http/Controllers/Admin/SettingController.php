<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function getDataKordinat()
    {
        $result = Setting::whereIn("key", ["longitude", "latitude", "radius"])->get();

        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);
    }

    public function updateDataKordinat()
    {
        $validator = Validator::make(request()->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'error',
                'data' => $validator->errors()
            ]);
        }

        $latitude = request('latitude');
        $longitude = request('longitude');
        $radius = request('radius');

        DB::table('settings')
            ->whereIn("key", ["latitude", "longitude", "radius"])
            ->update([
                'value' => DB::raw("CASE
                    WHEN `key` = 'latitude' THEN '$latitude'
                    WHEN `key` = 'longitude' THEN '$longitude'
                    WHEN `key` = 'radius' THEN '$radius'
                END")
            ]);

        $result = Setting::whereIn("key", ["latitude", "longitude", "radius"])->get();

        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);
    }

    public function indexBatasWaktu()
    {
        $result = Setting::whereIn("key", ["batas_waktu_absen_masuk", "batas_waktu_absen_pulang"])
            ->get()
            ->groupBy("key");

        foreach ($result as $key => $value) {
            $result[$key] = $value[0]->value;
        }

        if (!$result) {
            return response()->json([
                'message' => 'error',
                'data' => 'data not found'
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);
    }

    public function updateBatasWaktu(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'waktu_masuk' => 'required|date_format:H:i:s',
            'waktu_pulang' => 'required|date_format:H:i:s'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'error',
                'data' => $validator->errors()
            ], 400);
        }

        $waktuMasuk = Setting::where("key", "batas_waktu_absen_masuk")->first();
        $waktuPulang = Setting::where("key", "batas_waktu_absen_pulang")->first();

        $waktuMasuk->value = $request->waktu_masuk;
        $waktuMasuk->save();

        $waktuPulang->value = $request->waktu_pulang;
        $waktuPulang->save();

        return response()->json([
            'message' => 'success',
            'data' => [
                'waktu_masuk' => $waktuMasuk,
                'waktu_pulang' => $waktuPulang
            ]
        ]);
    }
}
