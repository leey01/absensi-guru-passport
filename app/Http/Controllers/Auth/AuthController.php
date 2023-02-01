<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('niy', $request->niy)->first();

        if ($user->niy == $request->niy && Hash::check($request->password, $user->password)){
            $token = $user->createToken($user->nama)->accessToken;

            return response()->json([
                'messege' => 'success',
                'user' => $user,
                'token' => $token
            ], 200);

        }

        return response()->json([
            'messege' => 'UNAUTHORIZED'
        ], 401);

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return [
            'message' => 'user logged out'
        ];
    }
}
