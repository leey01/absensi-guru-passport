<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ProfileResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::where('id', Auth::user()->id)->first();
        return new ProfileResource($user);
    }

    public function show()
    {
        $user = User::where('id', Auth::user()->id)->first();
        return response()->json([
            'message' => 'success',
            'data' => $user
        ], 200);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required',
        ]);
        try {
            if (!Hash::check($request->password_lama, Auth::user()->password)) {
                return response()->json([
                    'message' => 'password lama tidak sesuai',
                ], 400);
            } else {
                    $user = User::where('id', Auth::user()->id)->first();
                    $user->password = Hash::make($request->password_baru);
                    $user->save();
                    $user->tokens()->delete();
                    $token = $user->createToken('token-name')->plainTextToken;
                    return response()->json([
                        'message' => 'success',
                        'token' => $token
                    ], 200);

                }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error',
            ], 400);
        }
    }

}