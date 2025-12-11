<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function users(){
        $user = Auth::user();
        if ($user->role === 'admin') {
            $users = User::all();
            return response()->json(['users'=>$users]);
        }
        return response()->json(['message'=>'forbidden'], 403);
    }
}
