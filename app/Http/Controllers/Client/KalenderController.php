<?php

namespace App\Http\Controllers\Client;

use App\Models\Kalender;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class KalenderController extends Controller
{
    public function index()
    {
        $events = array();
        $bookings = Kalender::all();
        foreach ($bookings as $booking) {
            $events[] = [
                'id'=>$booking->id,
                'user_id'=>$booking->user_id,
                'judul'=>$booking->judul,
                'deskripsi'=>$booking->deskripsi,
                'tanggal'=>$booking->tanggal,
                'untuk'=>$booking->untuk,
                'is_libur'=>$booking->is_libur
            ];
        }

        return response()->json(['events'=>$events]);
    }

    public function otherDate(Request $request)
    {
        $tanggal = $request->tanggal;

        $events = DB::table('kalenders')
            ->whereDate('tanggal', $tanggal)
            ->get();

        return response()->json([
            'data' => $events
        ]);


    }

    public function notifEvent()
    {
        $event = null;
        $checkEvent = DB::table('kalenders')
            ->whereDate('tanggal', Carbon::now())
            ->exists();

        if ($checkEvent) {
            $event = DB::table('kalenders')
                ->whereDate('tanggal', Carbon::now())
                ->get();
        }

        return response()->json([
            'message' => 'event hari ini',
            'data' => $event
        ]);
    }
}
