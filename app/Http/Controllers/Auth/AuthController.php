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

        if (!$user) {
            return response()->json([
                'messege' => 'user not found'
            ], 404);
        }

        if ($user->niy == $request->niy && Hash::check($request->password, $user->password)){
            $token = $user->createToken('token-name')->plainTextToken;

            return response()->json([
                'messege' => 'success',
                'token' => $token
            ], 200);
        } else {
            return response()->json([
                'messege' => 'wrong pass',
            ], 400);
        }



    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return [
            'message' => 'user logged out'
        ];
    }
}
