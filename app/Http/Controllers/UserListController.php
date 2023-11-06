<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $latestTrack = Track::where('user_id', $userId)->get();

        return view('admin.show', compact('user', 'latestTrack'));
    }

    public function makePosition(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:255'
        ]);

        if ($validator->fails()) return $this->error_response2($validator->errors()->first());


        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->position = $request->input('position');
        $user->save();

        $message = [
            'uz' => 'Position updated successfully',
            'ru' => 'Position updated successfully',
            'en' => 'Position updated successfully',
        ];

        return $this->success_response($user, $message);
    }

}
