<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

//use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

//use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) return $this->error_response2($validator->errors()->first());

        $data = $request->only('name', "email");
        $data['password'] = Hash::make($request->input('password'));
        $data['phone'] = preg_replace('/[^0-9]/', '', $request->get('phone'));
        $user = User::create($data);
        $this->createTeam($user);

        $device = substr($request->userAgent() ?? '', 0, 255);
        $user['token'] = $user->createToken($device)->plainTextToken;

        $user->roles()->attach(3);

        $message = [
            "uz" => "Foydalanuvchi yaratildi",
            "ru" => "Пользователь был создан",
            "en" => "The user has been created",
        ];
        return $this->success_response($user, $message);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        if (!Auth::attempt($credentials)) {
            $message = [
                "uz" => "Noto'g'ri kirish ma'lumotlari",
                "ru" => "Неверные данные для входа",
                "en" => "Invalid login details",
            ];

            return $this->error_response2($message);
        }

        $user = User::where('phone', $credentials['phone'])->with('roles')->firstOrFail();

        auth()->user()->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;


        $user['token'] = $token;
        $result = $user;
        $message = [
            "uz" => "Foydalanuvchi tizimga kirdi",
            "ru" => "Пользователь вошёл в систему",
            "en" => "The user has logged in",
        ];
        return $this->success_response($result, $message);
    }

    public function info()
    {
        $users = User::all();

        return response()->json([
            'users' => $users
        ]);
    }

    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }

}
