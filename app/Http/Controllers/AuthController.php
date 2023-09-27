<?php

namespace App\Http\Controllers;


use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => "required|exists:users,phone"
        ]);
        if ($validator->fails()) {
            return $this->error_response2([
                "uz" => $validator->errors()->first(),
                "ru" => $validator->errors()->first(),
                "en" => $validator->errors()->first(),
            ]);
        }
        $code = random_int(1000, 9999);
        $verification = UserVerification::updateOrCreate(
            ['phone' => $request->phone],
            [
                'code' => Crypt::encrypt($code),
                'app_id' => null,
                'code_attempts' => 5
            ]
        );
        $this->sendVerificationCode($verification->phone, $code);

        return $this->success_response([], [
            "uz" => "Tekshirish kodi muvaffaqiyatli yuborildi.",
            "ru" => "Код подтверждения успешно отправлен.",
            "en" => "Verification code sent successfully.",
        ]);
    }


    protected function sendVerificationCode($phone, $code)
    {
        $result = Http::withBasicAuth('admin', 'admin')
            ->post("https://quramiz.uz/api/sendSMS", [
                'phone' => $phone,
                'content' => "Your verification code is: $code"
            ])->json();
        return $result;
    }


    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => "required|exists:user_verifications,phone",
            'code' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error_response([], $validator->errors()->first());
        }
        $verificationData = UserVerification::where('phone', $request->phone)->where('code_attempts', '>', 0)->where('code', '!=', null)->first();
        if (!is_null($verificationData)) {
            if ((time() - strtotime($verificationData->updated_at)) > 120) {
                return $this->error_response([], "Kodning amal qilish muddati tugagan", "Срок действия кода истек", "The code has expired");
            }
            if (Crypt::decrypt($verificationData->code) == $request->code) {
                $verificationData->update([
                    'code_attempts' => 5,
                    'app_id' => uniqid(),
                    'code' => null
                ]);
                return $this->success_response(['app_id' => $verificationData->app_id], 'success');
            } else {
                $verificationData->update(['code_attempts' => $verificationData->code_attempts - 1]);
                return $this->error_response([], "Noto'g'ri verifikatsiya kod", "Неверный код верификации", "Invalid verification code");
            }
        }
        return $this->error_response([], "Iltimos otp kodni qayta junatishni bosing.");
    }


// 3. Get New Password and Save
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                Rule::exists('user_verifications')->where(fn($q) => $q->where(['phone' => $request->phone, 'app_id' => $request->app_id])),
            ],
            'password' => 'required|string|min:8|confirmed',
            'app_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error_response([], $validator->errors()->first());
        }

        // Update the user's password
        User::where('phone', $request->phone)->update(['password' => Hash::make($request->password)]);

        // Fetch the user along with their roles
        $user = User::where('phone', $request->phone)->with('roles')->firstOrFail();

        $message = [
            'uz' => 'Parolni qayta tiklash muvaffaqiyatli bajarildi',
            'ru' => 'Пароль успешно сброшен',
            'en' => 'Password reset successful',
        ];

        return $this->success_response($user, $message);
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
