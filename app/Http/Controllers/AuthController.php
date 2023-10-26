<?php

namespace App\Http\Controllers;


use App\Models\Team;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function create(Request $request)
    {
        try {
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

            $user->roles()->attach(1);

            $message = [
                "uz" => "Foydalanuvchi yaratildi",
                "ru" => "Пользователь был создан",
                "en" => "The user has been created",
            ];
            return $this->success_response($user, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('phone', 'password');
            if (!Auth::attempt($credentials)) {
                return $this->error_response([], "Noto'g'ri kirish ma'lumotlari", "Неверные данные для входа", "Invalid login details");
            }

            $user = User::where('phone', $credentials['phone'])->with('roles')->firstOrFail();

            // Create a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            $result = $user->only([
                'id', 'name', 'email', 'company_id', 'status', 'company_inn', 'phone', 'email_verified_at', 'two_factor_confirmed_at',
                'current_team_id', 'profile_photo_path', 'created_at', 'updated_at', 'profile_photo_url'
            ]);
            $result['token'] = $token;
            $result['role_id'] = $user->roles->first()->id;

            $message = [
                "uz" => "Foydalanuvchi tizimga kirdi",
                "ru" => "Пользователь вошёл в систему",
                "en" => "The user has logged in",
            ];

            return $this->success_response($result, $message);
        } catch (\Exception $e) {
            // Handle exceptions here if needed
            return $this->error_response([], "Xatolik yuz berdi", "Произошла ошибка", "An error occurred");
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    protected function sendVerificationCode($phone, $code)
    {
        try {
            $result = Http::withBasicAuth('admin', 'admin')
                ->post("https://quramiz.uz/api/sendSMS", [
                    'phone' => $phone,
                    'content' => "Your verification code is: $code"
                ])->json();
            return $result;
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function verifyCode(Request $request)
    {
        try {
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

            return $this->error_response([], "Sms kod tastiqlanmadi va urunishlar ko'payib ketdi.Birozdan so'ng qayta harakat qilib ko'ring");
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


// 3. Get New Password and Save
    public function resetPassword(Request $request)
    {
        try {
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

            // Update the user's password and fetch the user in a single query
            $user = User::where('phone', $request->phone)->first();
            if ($user) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            if (!$user) {
                return $this->error_response([], "User not found");
            }

            // Create a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            $message = [
                'uz' => 'Parolni qayta tiklash muvaffaqiyatli bajarildi',
                'ru' => 'Пароль успешно сброшен',
                'en' => 'Password reset successful',
            ];

            // Create the result array with the user's data and role_id
            $result = $user->only([
                'id', 'name', 'email', 'company_id', 'company_inn', 'phone', 'email_verified_at', 'two_factor_confirmed_at',
                'current_team_id', 'profile_photo_path', 'created_at', 'updated_at', 'profile_photo_url'
            ]);
            $result['role_id'] = $user->roles->first()->id; // Assuming a user has only one role
            $result['token'] = $token;

            return $this->success_response($result, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
