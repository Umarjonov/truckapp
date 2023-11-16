<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RankController extends Controller
{
    public function createRank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rank' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response([], $validator->errors()->first());
        }

        $position = Rank::create([
            'rank' => $request->input('rank'),
        ]);

        $message = [
            'uz' => 'Rank created successfully',
            'ru' => 'Rank created successfully',
            'en' => 'Rank created successfully',
        ];
        return $this->success_response($position, $message);
    }

    public function getAllRanks()
    {
        $positions = Rank::all();

        $message = [
            'uz' => 'All ranks retrieved successfully',
            'ru' => 'All ranks retrieved successfully',
            'en' => 'All ranks retrieved successfully',
        ];

        return $this->success_response($positions, $message);
    }
}
