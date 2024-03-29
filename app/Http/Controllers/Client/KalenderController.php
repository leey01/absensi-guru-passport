<?php

namespace App\Http\Controllers\Client;

use App\Events\NotifEvent;
use App\Jobs\NotifEventJob;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class KalenderController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'start_time' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $idUser = Auth::user()->id;

            $events = Event::whereHas('peserta', function ($query) use ($idUser) {
                $query->where('id', $idUser);
            })->where(function ($query) use ($request) {
                $query->whereRaw('DATE(waktu_mulai) >= ? AND DATE(waktu_mulai) <= ?', [$request->start_time, $request->end_time])
                    ->orWhereRaw('DATE(waktu_selesai) >= ? AND DATE(waktu_selesai) <= ?', [$request->start_time, $request->end_time]);
            })->get();

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

//        foreach ($events as $event) {
//            foreach ($event->peserta as $peserta) {
//                event(new NotifEvent($peserta->id, $event->id));
//            }
//        }

        // nge dispatch job untuk broadcast notif
        $eventJob = new NotifEventJob();
        $this->dispatch($eventJob);

        return response()->json([
            'message' => 'event hari ini',
            'data' => $events
        ]);
    }
}
