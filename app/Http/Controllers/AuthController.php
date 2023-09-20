<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Hash;

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

    // start forgot password

// 1. Validate Phone Number and Send SMS Code
    public function forgotPassword(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validatedData['phone']);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return $this->error_response2([
                "uz" => "Foydalanuvchi topilmadi",
                "ru" => "Пользователь не найден",
                "en" => "User not found",
            ]);
        }

        $verificationCode = rand(1000, 9999);
        $hashedVerificationCode = Hash::make($verificationCode); // Hash the verification code
        $user->verification_code = $hashedVerificationCode; // Save the hashed code
        $user->save();

        $this->sendVerificationCode($user->phone, $verificationCode);

        // Return a success response indicating that the code has been sent
        return $this->success_response("Verification code sent successfully");
    }

// Helper method to send SMS
    protected function sendVerificationCode($phone, $code)
    {
        // Find the user by phone number
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // Handle the case where the user is not found
            return $this->error_response2([
                "uz" => "Foydalanuvchi topilmadi",
                "ru" => "Пользователь не найден",
                "en" => "User not found",
            ]);
        }

        // Check if a verification code was sent recently (e.g., within the last 2 minutes)
        $cooldownMinutes = 2;
        $recentCodeSent = $user->verification_code_sent_at && now()->diffInMinutes($user->verification_code_sent_at) < $cooldownMinutes;

        if ($recentCodeSent) {
            // Handle the case where the code was sent too recently
            return $this->error_response2([
                "uz" => "Urunish ko'payib ketti",
                "ru" => "Отправка кода слишком часто недопустима",
                "en" => "Sending the code too frequently is not allowed",
            ]);
        }

        // Send the verification code
        $twilioClient = new Client(config('services.twilio.sid'), config('services.twilio.token'));

        $twilioClient->messages->create(
            $phone,
            [
                'from' => config('services.twilio.from'),
                'body' => "Your verification code is: $code",
            ]
        );

        // Update the timestamp for the last sent code
        $user->verification_code_sent_at = now();
        $user->save();
    }


// 2. Check Code
    public function verifyCode(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string',
            'code' => 'required|numeric',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validatedData['phone']);
        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($validatedData['code'], $user->verification_code)) {
            return $this->error_response2([
                "uz" => "Noto'g'ri verifikatsiya kod",
                "ru" => "Неверный код верификации",
                "en" => "Invalid verification code",
            ]);
        }

    }

// 3. Get New Password and Save
    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:8|same:password_confirm',
            'password_confirm' => 'required',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validatedData['phone']);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return $this->error_response2([
                "uz" => "Foydalanuvchi topilmadi",
                "ru" => "Пользователь не найден",
                "en" => "User not found",
            ]);
        }

        // Update the user's password
        $user->password = Hash::make($validatedData['password']);
        $user->verification_code = null; // Clear the verification code
        $user->save();

        return $this->success_response("Password reset successfully");
    }


//    end forgot password

    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }

}
