<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create(Request $request)
    {
       
        $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)]
        );

        # And make sure to use the plainTextToken property
        # Since this will return us the plain text token and then store the hashed value in the database
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->roles()->attach(3);

        return response()->json([
            "user" => $user,
            "token" => $token
        ]);
    }

    public function login(Request $request)
    {

        if (!Auth::attempt($request->only('phone', 'password')))
            return response()->json([
                'message' => 'Invalid login details',
                401
            ]);
// with('roles')->get()->
        $user = User::where('phone', $request->phone)->with('roles')->get()->firstOrFail();

        # Delete the existing tokens from the database and create a new one
        auth()->user()->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
