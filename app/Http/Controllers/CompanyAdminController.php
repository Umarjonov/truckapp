<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class CompanyAdminController extends Controller
{
    public function addCompanyAdmin(Request $request)
    {
        try {
            // Check if the authenticated user is a moderator
            $authenticatedUserRole = auth()->user()->roles->first();
            if ($authenticatedUserRole->id !== 1) {
                return $this->error_response2('Unauthorized. You do not have the required role.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'password' => 'required|string|min:8',
                'company_id' => 'nullable|integer', // Make sure it's nullable if not always required
                'company_inn' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

            $company = null;
            if ($request->has('company_id') && $request->has('company_inn')) {
                $company = Company::where('id', $request->input('company_id'))
                    ->where('company_inn', $request->input('company_inn'))
                    ->first();

                if (!$company) {
                    return $this->error_response2('Company not found');
                }
            }

            $data = $request->only('name', 'email', 'company_id', 'company_inn');
            $data['password'] = Hash::make($request->input('password'));
            $data['phone'] = preg_replace('/[^0-9]/', '', $request->get('phone'));

            $user = User::create($data);
            $this->createTeam($user);

            $device = substr($request->userAgent() ?? '', 0, 255);
            $user['token'] = $user->createToken($device)->plainTextToken;

            $user->roles()->attach(3);

            $message = [
                'uz' => 'Foydalanuvchi yaratildi',
                'ru' => 'Пользователь был создан',
                'en' => 'The user has been created',
            ];

            return $this->success_response($user, $message, 201);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }

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
