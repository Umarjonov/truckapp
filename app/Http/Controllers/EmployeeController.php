<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function adminAddHrOrManager(Request $request)
    {
        try {
            $authenticatedUser = auth()->user();
            $authenticatedUserRole = $authenticatedUser->roles->first();

            $allowedRoleIds = [1, 3, 4, 5];

            if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
                return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'password' => 'required|string|min:8',
                'company_id' => 'nullable|string',
                'roleId' => 'required|string',
                'company_inn' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

            // Check if the company_id and company_inn exist in your database
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

            $roleId = $request->input('roleId');

            $allowedRoleIds = [4, 5];

            if (!in_array($roleId, $allowedRoleIds)) {
                return $this->error_response2('Invalid role name. Allowed values are Hr and Manager.');
            }

            $user->roles()->attach($roleId);

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

    public function createAdminToUser(Request $request)
    {
        try {
            $authenticatedUser = auth()->user();
            $authenticatedUserRole = $authenticatedUser->roles->first();

            $allowedRoleIds = [1, 3, 4, 5];

            if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
                return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'password' => 'required|string|min:4',
                'company_id' => 'nullable|string',
                'company_inn' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

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

            $device = substr($request->userAgent() ?? '', 0, 255);
            $user['token'] = $user->createToken($device)->plainTextToken;

            // Attach role 4 or 5 to the user based on your conditions
            $user->roles()->attach(6); // Change to 4 or 5 as needed

            $message = [
                'uz' => 'Foydalanuvchi yaratildi',
                'ru' => 'Пользователь был создан',
                'en' => 'The user has been created',
            ];

            return $this->success_response($user, $message, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(Request $request, $userId)
    {
        try {

            $authenticatedUser = auth()->user();
            $authenticatedUserRole = $authenticatedUser->roles->first();

            $allowedRoleIds = [1, 3, 4, 5];

            if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
                return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
            }


            $user = User::find($userId);

            if (!$user) {
                return $this->error_response2('User not found');
            }
            $userRoleId = $user->roles->first()->id;

            if ($userRoleId === 6) {
                $user->delete();
            } else {
                return $this->error_response2('You cannot delete this user');
            }

            $message = [
                'uz' => 'Foydalanuvchi o\'chirildi',
                'ru' => 'Пользователь был удален',
                'en' => 'The user has been deleted',
            ];

            return $this->success_response($message, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
