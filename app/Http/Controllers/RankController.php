<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RankController extends Controller
{
//    public function createRank(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'rank' => 'required|string',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->error_response([], $validator->errors()->first());
//        }
//
//        $position = Rank::create([
//            'rank' => $request->input('rank'),
//        ]);
//
//        $message = [
//            'uz' => 'Rank created successfully',
//            'ru' => 'Rank created successfully',
//            'en' => 'Rank created successfully',
//        ];
//        return $this->success_response($position, $message);
//    }
    public function createRank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rank' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response([], $validator->errors()->first());
        }

        $authenticatedUser = auth()->user();
        $company_id = $authenticatedUser->company_id;

        if (!$company_id) {
            return $this->error_response([], 'We could not identify your company');
        }

        $rank = Rank::create([
            'rank' => $request->input('rank'),
            'company_id' => $company_id,
        ]);

        $message = [
            'uz' => 'Rank created successfully',
            'ru' => 'Rank created successfully',
            'en' => 'Rank created successfully',
        ];

        return $this->success_response($rank, $message);
    }

    public function getAllRanks()
    {
        $authenticatedUser = auth()->user();
        $company_id = $authenticatedUser->company_id;

        if (!$company_id) {
            return $this->error_response([], 'We could not identify your company');
        }

        $positions = Rank::whereHas('company', function ($query) use ($company_id) {
            $query->where('id', $company_id);
        })->get();

        $message = [
            'uz' => 'All positions retrieved successfully',
            'ru' => 'All positions retrieved successfully',
            'en' => 'All positions retrieved successfully',
        ];

        return $this->success_response($positions, $message);
    }
}
