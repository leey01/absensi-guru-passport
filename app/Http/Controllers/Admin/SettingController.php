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
}
