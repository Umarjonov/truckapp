<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

//use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        $existingUser = User::where('email', $request->input('email'))
            ->orWhere('phone', $request->input('phone'))
            ->first();

        if ($existingUser) {
            return response()->json([
                'message' => 'This user has already been created'
            ], 400);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->roles()->attach(3);

        return response()->json([
            "message" => "This user has been created",
            "user" => $user,
            "token" => $token
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('phone', $credentials['phone'])->with('roles')->firstOrFail();

        auth()->user()->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function info()
    {
        $users = User::all();

        return response()->json([
            'users' => $users
        ]);
    }
}
