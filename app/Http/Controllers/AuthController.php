<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request){
        // validálás
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' =>'required|min:2|confirmed', // password_confirmed
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        // nincs hiba
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('titkos')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request){
        $credentials = $request->validate([
            'email' =>'required|email',
            'password' => 'required'
        ]);

        // hitelesítés
        if (!Auth::attempt($credentials)){
           return response()->json(['message'=>'Hibás adatok!'],401);
        }

        $user = User::where('email',$request->email)->firstOrFail();
        $token = $user->createToken('titkos')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ],200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Sikeres kijelentkezés!'],200);
    }
}
