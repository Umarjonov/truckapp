<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Track;
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
        try {
            if ($usersWithRoles = User::all()) { // Assuming 'role' is the name of the relationship method.
                $message = [
                    'uz' => 'Foydalanuvchi ma\'lumotlari',
                    'ru' => 'Информация пользователя',
                    'en' => 'User Information'
                ];

                return $this->success_response($usersWithRoles, $message);
            } else {
                $errorResponse = [
                    'error' => [
                        'uz' => 'Xatolik yuz berdi',
                        'ru' => 'Произошла ошибка',
                        'en' => 'An error occurred'
                    ]
                ];

                return response()->json($errorResponse, 400); // Return a JSON response with a 400 status code (Bad Request)
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
