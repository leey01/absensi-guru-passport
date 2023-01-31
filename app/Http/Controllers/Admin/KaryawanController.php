<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class KaryawanController extends Controller
{

    public function index()
    {
        $pengajar = User::where('jenis_user', 'pengajar')->get();
        $staff = User::where('jenis_user', 'staff')->get();

        return response()->json(
            [
                'message' => 'succes',
                'pengajar' => $pengajar,
                'staff' => $staff,
            ]
        );

    }

    public function edit ($id)
    {
        $user = User::find($id);
        return response()->json([
            'message' => 'success',
            'user' => $user
        ]);
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'niy' => ['required'],
            'password' => ['required'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
            'jenis_user' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        } else{
            try {
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'niy' => $request->niy,
                'password' => $request->password,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'jenis_user' => $request->jenis_user,
                // 'pf_profile' => $request->file('pf_profile')->store('public/profile'),
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
        // get data
        $validator = Validator::make(request()->all(), [
            'nama' => ['required'],
            'email' => ['required', 'email'],
            'alamat' => ['required'],
            'no_hp' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'failed',
                'errors' => $validator->errors()
            ]);
        }
        try {
            $user = User::where('id', $request->id)->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'jenis_user' => $request->jenis_user,
                // 'pf_profile' => $request->file('pf_profile')->store('public/profile'),
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
//
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
