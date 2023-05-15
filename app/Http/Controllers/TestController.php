<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testTimeNow()
    {
        $timeNow = time();
        $nowNow = now();
        $carbonNow = \Carbon\Carbon::now();

        return response()->json([
            'timeNow' => $timeNow,
            'nowNow' => $nowNow,
            'carbonNow' => $carbonNow
        ]);
    }
}
