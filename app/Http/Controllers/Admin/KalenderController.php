<?php

namespace App\Http\Controllers\Admin;

use App\Imports\EventsImport;
use App\Models\Event;
use App\Models\KategoriKaryawan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class KalenderController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'bulan' => 'required',
            'tahun' => 'required'
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        $events = Event::whereYear('waktu_mulai', $request->tahun)
            ->whereMonth('waktu_mulai', $request->bulan)
            ->get();

        if ($events->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
                'data' => $events
            ]);
        }

        return response()->json([
            'messege' => 'success',
            'data' => $events
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'judul' => 'required',
            'waktu_mulai' => 'required'
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $event = Event::create([
                'user_id' => Auth::user()->id,
                'judul' => $request->judul,
                'lokasi' => $request->lokasi,
                'kategori_event' => $request->kategori_event,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'deskripsi' => $request->deskripsi,
            ]);

            $pesertas = $request->input('peserta');
            if (empty($pesertas)) {
                $pesertas = [];
            }
            foreach ($pesertas as $peserta) {
                DB::table('pesertas')->insert([
                    'event_id' => $event->id,
                    'user_id' => $peserta,
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        $result = Event::where('id', $event->id)->with('peserta')->first();

        return response()->json([
            'message' => 'success',
            'data' => $result
        ], 201);
    }

    public function update($id)
    {
        $event = Event::find($id);
        if (! $event) {
            return response()->json([
                'error'=>'event not found'
            ], 404);
        }

        $event->update([
            'judul' => request('judul'),
            'kategori_event' => request('kategori_event'),
            'lokasi' => request('lokasi'),
            'waktu_mulai' => request('waktu_mulai'),
            'waktu_selesai' => request('waktu_selesai'),
            'deskripsi' => request('deskripsi'),
        ]);

        $pesertas = request('peserta');
        if (empty($pesertas)) {
            $pesertas = [];
        } else {
            DB::table('pesertas')->where('event_id', $id)->delete();
        }

        foreach ($pesertas as $peserta) {
            DB::table('pesertas')->insert([
                'event_id' => $event->id,
                'user_id' => $peserta,
            ]);
        }

        $result = Event::where('id', $event->id)->with('peserta')->first();

        return response()->json([
            'message' => 'success',
            'data' => $result
        ], 200);
    }

    public function destroy($id)
    {
        $event = Event::find($id);
        if (! $event) {
            return response()->json([
                'message'=>'event not found'
            ], 404);
        }

        DB::table('pesertas')->where('event_id', $id)->delete();
        $event->delete();

        return response()->json([
            'message' => 'success'
        ], 200);
    }

    public function show($id)
    {
        $event = Event::where('id', $id)->with('peserta')->first();
        if (! $event) {
            return response()->json([
                'error'=>'event not found'
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'data' => $event
        ], 200);
    }

    public function getKategori()
    {
        $kategori = KategoriKaryawan::all();

        return response()->json([
            'message' => 'success',
            'data' => $kategori
        ]);
    }

    public function getKaryawan(Request $request)
    {
        $result = User::with('ktgkaryawan')->get();

        if (isset($request->search) ? true : false) {
            $result = User::with('ktgkaryawan')
                ->orWhere('nama', 'like', '%' . $request->search . '%')
                ->orWhereHas('ktgkaryawan', function ($query) use ($request) {
                    $query->where('kategori', 'like', '%' . $request->search . '%');
                })
                ->orWhere('niy', 'like', '%' . $request->search . '%')
                ->get();

        }

        if (isset($request->kategori_id) ? true : false) {
            $karyawans = User::with('ktgkaryawan')->get();
            $result = [];

            foreach ($karyawans as $karyawan) {
                foreach ($karyawan->ktgkaryawan as $ktg) {
                    if ($ktg->id == $request->kategori_id) {
                        $result = array_merge($result, [$karyawan]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);
    }

    public function importEvents(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'file' => ['required', 'mimes:xls,xlsx']
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        $file = $request->file('file');
        $nama_file = rand().$file->getClientOriginalName();
        $file->move('import/events', $nama_file);

        Excel::import(new EventsImport($request->user()->id), public_path('/import/events/'.$nama_file));

        return response()->json([
            'message' => 'success',
        ]);
    }
}
