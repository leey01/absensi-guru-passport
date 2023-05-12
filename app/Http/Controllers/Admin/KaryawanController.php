<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\KaryawanResource;
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

class KaryawanController extends Controller
{

    public function index(Request $request)
    {
        $result = [];

        if (isset($request->search) ? true : false) {
            $result = User::with('ktgkaryawan')
                ->orWhere('nama', 'like', '%' . $request->search . '%')
                ->orWhereHas('ktgkaryawan', function ($query) use ($request) {
                    $query->where('kategori', 'like', '%' . $request->search . '%');
                })
                ->orWhere('niy', 'like', '%' . $request->search . '%')
                ->get();
        }


        $karyawans = User::with('ktgkaryawan')->get();

        foreach ($karyawans as $karyawan) {
            foreach ($karyawan->ktgkaryawan as $ktg) {
                if ($ktg->id == $request->kategori_id) {
                    $result = array_merge($result, [$karyawan]);
                }
            }
        }

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

    public function storeUser(Request $request)
    {
        $default_password = 'smkrus';
        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
            'niy' => ['required'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
            'pf_foto' => ['image:jpeg,png,jpg', 'file']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        } else {
            try {
//            $image_path = $request->file('pf_foto')->store('/profile');

                $foto_path = 'profile/' . time() . $request->pf_foto->getClientOriginalName();
                Storage::disk('public')->put($foto_path, file_get_contents($request->pf_foto));
                $image_path = Storage::disk('public')->url($foto_path);

                $user = User::create([
                    'nama' => $request->nama,
                    'email' => $request->email,
                    'niy' => $request->niy,
                    'password' => Hash::make($default_password),
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp,
                    'pf_foto' => $foto_path
                ]);

                return response()->json([
                    'message' => 'success',
                    'user' => $user
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'failed',
                    'errors' => $th->getMessage()
                ]);
            }
        }

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
//            'pf_foto' => ['image:jpeg,png,jpg', 'file'],
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
        $data = $request->input('jadwal');
        foreach ($data as $item) {
            Jadwal::create([
                'user_id' => $user->id,
                'hari' => $item['hari'],
                'jam_masuk' => $item['jam_masuk'],
                'jam_pulang' => $item['jam_pulang'],
            ]);
        }


        // create kategori
        $req = $request->input('ktg_karyawan');
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

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['message' => 'Id not found'], 404);
        }

        // get data
        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
            'pf_foto' => ['image:jpeg,png,jpg', 'file']
        ]);

//        $image_path = $request->file('pf_foto')->store('/profile');

        if ($validator->fails()) {
            return response()->json([
                'message' => 'failed',
                'errors' => $validator->errors()
            ]);
        }

        if ($request->hasFile('pf_foto')) {
            File::delete($user->pf_foto);
        }

        try {
//            $image_path = $request->file('pf_foto')->store('/profile');

            $foto_path = 'profile/' . time() . $request->pf_foto->getClientOriginalName();
            Storage::disk('public')->put($foto_path, file_get_contents($request->pf_foto));
            $image_path = Storage::disk('public')->url($foto_path);

            $user = User::where('id', $request->id)->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'jenis_user' => $request->jenis_user,
                'pf_foto' => $foto_path,
            ]);
            return response()->json([
                'message' => 'success',
                'data' => $validator->validated()
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'errors' => $th->getMessage()
            ]);
        }
    }


    public function delete($id)
    {
        if (!User::find($id)) {return Response::json(['message' => 'Id not found'], 404);}
        try {
            DB::table('kategori_karyawan_users')->where('user_id', $id)->delete();
            Jadwal::where('user_id', $id)->delete();
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

    public function testUpdate(Request $request)
    {
        $data = $request->input('jadwal');

        try {
            foreach ($data as $item) {
                Jadwal::create([
                    'user_id' => $request->user_id,
                    'hari' => $item['hari'],
                    'jam_masuk' => $item['jam_masuk'],
                    'jam_pulang' => $item['jam_pulang'],
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'errors' => $th->getMessage()
            ]);
        }

//        Jadwal::create([
//            'user_id' => $request->user_id,
//            'hari' => 'Senin',
//            'jam_masuk' => $request->input('senin.jam_masuk'),
//            'jam_pulang' => $request->input('senin.jam_pulang'),
//        ]);

        $result = Jadwal::where('user_id', $request->user_id)->get();
        return response()->json([
            'message' => 'success',
            'data' => $result
        ]);
    }

    public function testKategori(Request $request, $id)
    {
        $user = User::with(['ktgkaryawan'])->find($id);
        $req = $request->input('ktg_karyawan');

        DB::table('kategori_karyawan_users')
            ->where('user_id', $id)
            ->delete();

        foreach ($req as $item) {
            DB::table('kategori_karyawan_users')->insert([
                'user_id' => $id,
                'kategori_id' => $item
            ]);
        }

        return response()->json([
            'message' => 'success',
            'data' => $req
        ]);
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
        Jadwal::where('user_id', $id)->delete();
        $data = $request->input('jadwal');
        foreach ($data as $item) {
            Jadwal::create([
                'user_id' => $id,
                'hari' => $item['hari'],
                'jam_masuk' => $item['jam_masuk'],
                'jam_pulang' => $item['jam_pulang'],
            ]);
        }

        // update kategori
        $req = $request->input('ktg_karyawan');
        DB::table('kategori_karyawan_users')
            ->where('user_id', $id)
            ->delete();

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
}
