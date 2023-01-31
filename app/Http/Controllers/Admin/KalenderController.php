<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kalender;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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

    public function store(Request $request)
    {
        $request->validate([
            'judul'=>'required|string|max:255',
          ]);
          $booking = Kalender::create([
                  'user_id'=> Auth::user()->id,
                  'judul'=>$request->judul,
                  'deskripsi'=>$request->deskripsi,
                  'tanggal'=>$request->tanggal,
                  'untuk'=>$request->untuk,
                  'is_libur'=>$request->is_libur,
          ]);

          return response()->json($booking);
    }

    public function update($id)
    {
        $booking = Kalender::find($id);

        return response()->json([
            'message'=>'Data event',
            'data' => $booking
        ]);
    }

    public function edit(Request $request)
    {
       $request->validate([
        'judul'=>'required|string|max:255',
      ]);

       $booking = Kalender::find($request->id);
       $booking->judul = $request->judul;
       $booking->tanggal = $request->tanggal;
       $booking->deskripsi = $request->deskripsi;
       $booking->untuk = $request->untuk;
       $booking->is_libur = $request->is_libur;
       $booking->save();

        return response()->json([
            'status'=>'success',
            'message'=>'Data berhasil diubah',
            'data'=> $booking
        ]);
    }

    public function destroy($id)
    {
      $booking = Kalender::find($id);
      if (! $booking) {
        return response()->json([
          'error'=>'Unable to locate the event'
        ], 404);
      }
      $booking->delete();
      return response()->json(['message'=>'Data berhasil dihapus']);
    }
}
