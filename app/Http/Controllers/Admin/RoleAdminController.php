<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleAdminController extends Controller
{
    public function index()
    {
        $data = RoleAdmin::with('kategori')->get();

        return response()->json([
            'message' => 'Role Admin',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id.*' => 'required|unique:role_admins,kategori_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Role Admin sudah tersedia',
                'errors' => $validator->errors(),
            ], 400);
        }


        foreach ($request->kategori_id as $id) {
            $data = RoleAdmin::create([
                'kategori_id' => $id,
            ]);
        }

        return response()->json([
            'message' => 'Role Admin berhasil ditambahkan',
            'data' => $data,
        ]);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id.*' => 'required|exists:role_admins,kategori_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Role Admin tidak ditemukan',
                'errors' => $validator->errors(),
            ], 400);
        }

        $kategori_id = $request->kategori_id;

        foreach ($kategori_id as $id) {
            $data = RoleAdmin::where('kategori_id', $id)->first();
            $data->delete();
        }

        return response()->json([
            'message' => 'Role Admin berhasil dihapus',
        ]);
    }
}
