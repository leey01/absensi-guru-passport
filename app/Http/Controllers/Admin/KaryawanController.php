<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\KaryawanResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
{

    public function index(Request $request)
    {

        if (isset($request->search) ? true : false)
        {
            // search user pengajar by name and niy
            $pengajar = User::where('jenis_user', 'pengajar')->where(function($query) use ($request){
                $query->where('nama', 'like', '%'.$request->search.'%')
                    ->orWhere('niy', 'like', '%'.$request->search.'%');
            })->get();

            // search user staff by name and niy
            $staff = User::where('jenis_user', 'staff')->where(function($query) use ($request){
                $query->where('nama', 'like', '%'.$request->search.'%')
                    ->orWhere('niy', 'like', '%'.$request->search.'%');
            })->get();

            return response()->json(
                [
                    'message' => 'succes',
                    'pengajar' => KaryawanResource::collection($pengajar),
                    'staff' => KaryawanResource::collection($staff),
                ]
            );
        }

        $pengajar = User::where('jenis_user', 'pengajar')->get();
        $staff = User::where('jenis_user', 'staff')->get();

        return response()->json(
            [
                'message' => 'succes',
                'pengajar' => KaryawanResource::collection($pengajar),
                'staff' => KaryawanResource::collection($staff),
            ]
        );

    }

    public function edit ($id)
    {
        $user = User::find($id);
        return response()->json([
            'message' => 'success',
            'user' => new KaryawanResource($user)
        ]);
    }

    public function storeUser(Request $request)
    {
        $default_password = 'smkrus';
        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'niy' => ['required'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
            'jenis_user' => ['required'],
            'pf_foto' => ['image:jpeg,png,jpg', 'file']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        } else{
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
                'jenis_user' => $request->jenis_user,
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


    public function updateUser(Request $request)
    {
        $user = User::find($request->id);

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

            $foto_path = '/profile' . time() . $request->pf_foto->getClientOriginalName();
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


    public function deleteUser($id)
    {
        try {
            $user = User::where('id', $id)->delete();
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

}
