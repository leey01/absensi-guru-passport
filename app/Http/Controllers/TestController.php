<?php

namespace App\Http\Controllers;

use App\Events\NotifEvent;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
            'carbonNow' => $carbonNow,
            'format' => $carbonNow->format('Y-m-d H:i:s')
        ]);
    }

    public function testNotifEvent()
    {
        $events = Event::with('peserta')
            ->get();

        foreach ($events as $event) {
            foreach ($event->peserta as $peserta) {
                event(new NotifEvent($peserta->id, $event->id));
            }
        }

        return response()->json([
            'message' => 'event hari ini',
            'data' => $events
        ]);
    }
}
