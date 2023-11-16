<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function createPosition(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'position' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response([], $validator->errors()->first());
        }

        $position = Position::create([
            'position' => $request->input('position'),
        ]);

        $message = [
            'uz' => 'Position created successfully',
            'ru' => 'Position created successfully',
            'en' => 'Position created successfully',
        ];
        return $this->success_response($position, $message);
    }

    public function getAllPositions()
    {
        $positions = Position::all();

        $message = [
            'uz' => 'All positions retrieved successfully',
            'ru' => 'All positions retrieved successfully',
            'en' => 'All positions retrieved successfully',
        ];

        return $this->success_response($positions, $message);
    }


}
