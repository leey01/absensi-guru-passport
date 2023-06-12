<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriKaryawan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;

class KategoriKaryawanController extends Controller
{
    public function index()
    {
        $data = KategoriKaryawan::all();

        foreach ($data as $kategori) {
            $kategori->jumlah = DB::table('kategori_karyawan_users')
                ->where('kategori_id', $kategori->id)
                ->count();
        }

        return response()->json([
            'message' => 'success',
            'data' => $data
        ]);
    }

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'nama_kategori' => 'required|unique:kategori_karyawans,kategori'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'error',
                'data' => $validator->errors()
            ]);
        }

        try {
            $kategori = KategoriKaryawan::create([
                'kategori' => request('nama_kategori')
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success',
            'data' => $kategori
        ]);
    }

    public function update($id)
    {
        $validator = Validator::make(request()->all(), [
            'nama_kategori' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'error',
                'data' => $validator->errors()
            ]);
        }

        try {
            $kategori = KategoriKaryawan::find($id);
            if ($kategori) {
                $kategori->kategori = request('nama_kategori');
                $kategori->save();

            } else {
                return response()->json([
                    'message' => 'id not found',
                ], 404);
            }

        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success',
            'data' => $kategori
        ]);
    }

    public function delete($id)
    {
        try {
            $kategori = KategoriKaryawan::find($id);

            if ($kategori) {
                DB::table('kategori_karyawan_users')
                    ->where('kategori_id', $id)
                    ->delete();
                $kategori->delete();
            } else {
                return response()->json([
                    'message' => 'id not found',
                ], 404);
            }


        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function getAllKaryawan(Request $request)
    {
        $search = $request->search;

        if ($search) {
            $data = User::with('ktgkaryawan')
                ->where('nama', 'like', "%$search%")
                ->orWhere('niy', 'like', "%$search%")
                ->get();
        } else {
            $data = User::with('ktgkaryawan')->get();
        }

        return response()->json([
            'message' => 'success',
            'data' => $data
        ]);
    }

    public function assignKategori()
    {
        $validator = Validator::make(request()->all(), [
            'kategori_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'error',
                'data' => $validator->errors()
            ], 400);
        }

        $karyawans = request('karyawan_id');
        $kategori = request('kategori_id');

        try {
            DB::table('kategori_karyawan_users')
                ->where('kategori_id', $kategori)
                ->delete();

            foreach ($karyawans as $karyawan) {
                DB::table('kategori_karyawan_users')
                    ->insert([
                        'kategori_id' => $kategori,
                        'user_id' => $karyawan
                    ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function unAssignKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unassign.*.user_id' => 'required',
            'unassign.*.kategori_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'error',
                'data' => $validator->errors()
            ], 400);
        }

        $remove = $request->unassign;

        try {
            foreach ($remove as $rmv) {
                DB::table('kategori_karyawan_users')
                    ->where('user_id', $rmv['user_id'])
                    ->where('kategori_id', $rmv['kategori_id'])
                    ->delete();
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Failed",
                'data' => $e
            ], 503);
        }

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function show($id)
    {
        $kategori = KategoriKaryawan::where('id', $id)
            ->with('users')
            ->get();

        if (!$kategori) {
            return response()->json([
                'message' => 'id not found'
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'data' => $kategori
        ]);
    }
}
