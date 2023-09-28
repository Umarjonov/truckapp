<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Your logic for the admin dashboard
        // Only users with 'admin' role can access this route
    }

    public function info()
    {
        $user = auth()->user();
        if ($user->role_user->role_id === 3) {
            $usersWithRoles = User::with('role_user')->get();
            $message = [
                'uz' => 'Foydalanuvchi ma\'lumotlari',
                'ru' => 'Информация пользователя',
                'en' => 'User Information'
            ];

            return $this->success_response($usersWithRoles, $message);
        } else {
            return $this->error_response('Unauthorized', 403);
        }
    }


}
