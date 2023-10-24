<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::paginate(10); // 10 users per page

        return view('users.index', ['users' => $users]);
    }

    public function admin()
    {
        return view('admin.admin');
    }

    public function list()
    {
        return view('admin.userlist');
    }


}
