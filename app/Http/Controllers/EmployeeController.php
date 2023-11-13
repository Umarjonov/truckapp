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
        $authenticatedUser = auth()->user();
        $authenticatedUserRole = $authenticatedUser->roles->first();

        $allowedRoleIds = [1, 3];

        if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
            return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
        }

        if (!$authenticatedUser->company_id || !$authenticatedUser->company_inn) {
            return $this->error_response2('You are not associated with any company.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $company_inn = $authenticatedUser->company_inn;
        $company_id = $authenticatedUser->company_id;

        $data = $request->only('name', 'email');
        $data['password'] = Hash::make($request->input('password'));
        $data['phone'] = preg_replace('/[^0-9]/', '', $request->get('phone'));

        $data['company_inn'] = $company_inn;
        $data['company_id'] = $company_id;

        $user = User::create($data);
        $this->createTeam($user);

        $device = substr($request->userAgent() ?? '', 0, 255);
        $user['token'] = $user->createToken($device)->plainTextToken;

        $role_id = 5; // Change this to the appropriate role_id

        $user->roles()->attach($role_id);
        $user['role_id'] = $role_id;

        $message = [
            'uz' => 'Foydalanuvchi yaratildi',
            'ru' => 'Пользователь был создан',
            'en' => 'The user has been created',
        ];

        return $this->success_response($user, $message, 201);
    }


    public function createAdminToUser(Request $request)
    {
        $authenticatedUser = auth()->user();
        $authenticatedUserRole = $authenticatedUser->roles->first();

        $allowedRoleIds = [1, 3, 4, 5];

        if (!in_array($authenticatedUserRole->id, $allowedRoleIds)) {
            return $this->error_response2('Unauthorized. You do not have the required role to view company users.');
        }

        if (!$authenticatedUser->company_id || !$authenticatedUser->company_inn) {
            return $this->error_response2('You are not associated with any company.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $company_inn = $authenticatedUser->company_inn;
        $company_id = $authenticatedUser->company_id;


        $data = $request->only('name', 'email');
        $data['password'] = Hash::make($request->input('password'));
        $data['phone'] = preg_replace('/[^0-9]/', '', $request->get('phone'));

        $data['company_inn'] = $company_inn;
        $data['company_id'] = $company_id;

        $user = User::create($data);
        $this->createTeam($user);

        $device = substr($request->userAgent() ?? '', 0, 255);
        $user['token'] = $user->createToken($device)->plainTextToken;

        $role_id = 6;
        $user->roles()->attach($role_id);
        $user['role_id'] = $role_id;

        $message = [
            'uz' => 'Foydalanuvchi yaratildi',
            'ru' => 'Пользователь был создан',
            'en' => 'The user has been created',
        ];

        return $this->success_response($user, $message, 201);

    }

    public function deleteUser(Request $request, $userId)
    {


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
    }

//    ============= TEST ====================


}
