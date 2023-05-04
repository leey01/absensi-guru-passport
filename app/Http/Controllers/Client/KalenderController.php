<?php

namespace App\Http\Controllers\Client;

use App\Events\NotifEvent;
use App\Models\Event;
use App\Models\Peserta;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class KalenderController extends Controller
{
    public function index()
    {
        try {
            $events = User::where('id', auth()->user()->id)
                ->with('event')
                ->first();

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success',
            'data' => $events
        ]);
    }

    public function show($id)
    {
        try {

            $event = Event::with('peserta')
                ->find($id);

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success',
            'data' => $event
        ]);
    }

    public function notifEvent()
    {
        $event = array();
        $checkEvent = DB::table('kalenders')
            // ->whereDate('tanggal', Carbon::now())
            ->whereDate('tanggal', '>=', today()->toDateString())
            ->exists();

        if ($checkEvent) {
            $event = DB::table('kalenders')
                // ->whereDate('tanggal', Carbon::now())
                ->whereDate('tanggal', '>=', today()->toDateString())
                ->get();
        }

        return response()->json([
            'message' => 'event hari ini',
            'data' => $event
        ]);
    }

    public function notifEventToday()
    {
        $events = Event::with('peserta')
            ->whereDate('waktu_mulai', Carbon::now())
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
