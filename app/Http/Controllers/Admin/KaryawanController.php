<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\KaryawanResource;
use App\Imports\UsersImport;
use App\Models\Jadwal;
use App\Models\KategoriKaryawan;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{

    public function index(Request $request)
    {
        $result = array();

        if (isset($request->search) ? true : false) {
            $result = User::with('ktgkaryawan')
                ->orWhere('nama', 'like', '%' . $request->search . '%')
                ->orWhereHas('ktgkaryawan', function ($query) use ($request) {
                    $query->where('kategori', 'like', '%' . $request->search . '%');
                })
                ->orWhere('niy', 'like', '%' . $request->search . '%')
                ->get();
            $result = $result->toArray();
        }


        $karyawans = User::with('ktgkaryawan')->get();

        if (isset($request->kategori_id) ? true : false) {
            foreach ($karyawans as $karyawan) {
                foreach ($karyawan->ktgkaryawan as $ktg) {
                    if ($ktg->id == $request->kategori_id) {
                        $result = array_merge($result, [$karyawan]);
                    }
                }
            }
        }

        $result = $karyawans;

        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);

    }

    public function getKategori()
    {
        $kategori = KategoriKaryawan::all();

        return response()->json([
            'message' => 'success',
            'data' => $kategori
        ]);
    }

    public function show($id)
    {
        $user = User::with(['ktgkaryawan', 'jadwal'])->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'failed',
                'data' => 'User not found'
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
            'niy' => ['required'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
            'pf_foto' => ['image:jpeg,png,jpg', 'file'],
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        // create user
        $foto_path = null;
        if (isset($request->pf_foto)) {
            $foto_path = 'profile/' . time() . $request->pf_foto->getClientOriginalName();
            Storage::disk('public')->put($foto_path, file_get_contents($request->pf_foto));
        }
        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'niy' => $request->niy,
            'password' => Hash::make($request->password),
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'pf_foto' => $foto_path
        ]);

        // create jadwal
        $data = $request->input('jadwal', []);

        foreach ($data as $item) {
            Jadwal::create([
                'user_id' => $user->id,
                'hari' => $item['hari'],
                'jam_masuk' => $item['jam_masuk'],
                'jam_pulang' => $item['jam_pulang'],
            ]);
        }


        // create kategori
        $req = $request->input('ktg_karyawan', []);
        if (empty($req)){
            $req = [];
        }
        foreach ($req as $item) {
            DB::table('kategori_karyawan_users')->insert([
                'user_id' => $user->id,
                'kategori_id' => $item
            ]);
        }

        $result = User::with(['jadwal', 'ktgkaryawan'])->find($user->id);
        return response()->json([
            'message' => 'success',
            'user' => $result
        ]);
    }

    public function delete($id)
    {
        if (!User::find($id)) {return Response::json(['message' => 'Id not found'], 404);}
        try {
            DB::table('kategori_karyawan_users')->where('user_id', $id)->delete();
            Jadwal::where('user_id', $id)->delete();
            DB::table('pesertas')->where('user_id', $id)->delete();
            User::where('id', $id)->delete();
            return response()->json([
                'message' => 'success delete',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'errors' => $th->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['message' => 'Id not found'], 404);
        }

        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
        ]);

        if ($validator->fails()){
            return response()->json([
                'message' => 'wrong required parameter',
                'data' => $validator->errors()
            ], 400);
        }

        // update foto profile
        if ($request->hasFile('pf_foto')) {
            File::delete($user->pf_foto);
        }

        $foto_path = $user->pf_foto;
        if (isset($request->pf_foto)) {
            $foto_path = 'profile/' . time() . $request->pf_foto->getClientOriginalName();
            Storage::disk('public')->put($foto_path, file_get_contents($request->pf_foto));
        }

        // update user
        $pass = $user->password;
        if (isset($request->password)) {
            $pass = Hash::make($request->password);
        }
        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => $pass,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'pf_foto' => $foto_path,
        ]);

        // update jadwal
        $data = $request->input('jadwal', []);
        if (empty($data)) {
            $data = [];
        } else {
            Jadwal::where('user_id', $id)->delete();
        }
        foreach ($data as $item) {
            Jadwal::create([
                'user_id' => $id,
                'hari' => $item['hari'],
                'jam_masuk' => $item['jam_masuk'],
                'jam_pulang' => $item['jam_pulang'],
            ]);
        }

        // update kategori
        $req = $request->input('ktg_karyawan', []);
        if (empty($req)) {
            $req = [];
        } else {
            DB::table('kategori_karyawan_users')
                ->where('user_id', $id)
                ->delete();
        }

        foreach ($req as $item) {
            DB::table('kategori_karyawan_users')->insert([
                'user_id' => $id,
                'kategori_id' => $item
            ]);
        }

        $result = User::with(['jadwal', 'ktgkaryawan'])->find($id);
        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);
    }

    public function import(Request $request)
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
        $file->move('import/users', $nama_file);

        Excel::import(new UsersImport, public_path('/import/users/'.$nama_file));

        return response()->json([
            'message' => 'success',
        ]);
    }
}
