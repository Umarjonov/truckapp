<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;

class UserListController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        if (auth()->user()->hasRole('Admin'))
        return view('admin.index2', ['users' => $users]);

        return redirect()->route('dashboard');
    }

//    public function show($user)
//    {
//        $user = User::find($user);
//        return view('admin.show', [
//            'user' => $user
//        ]);
//    }
    public function show($userId)
    {
        $user = User::find($userId);

        // Retrieve the latest latitude and longitude for the user from the Track model
        $latestTrack = Track::where('user_id', $userId)->get();

        return view('admin.show', compact('user','latestTrack' ));
    }
}
