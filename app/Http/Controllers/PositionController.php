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
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response([], $validator->errors()->first());
        }

        $company_id = auth()->user()->company_id;

        if (!$company_id) {
            return $this->error_response([], 'We could not identify your company.');
        }

        $positionData = [
            'position' => $request->input('position'),
            'company_id' => $company_id,
            'status' => $request->input('status', false), // Set default to false if not provided
        ];

        $position = Position::create($positionData);

        $message = 'Position created successfully';

        return $this->success_response($position, $message);
    }


    public function getAllPositions()
    {
        $authenticatedUser = auth()->user();
        $company_id = $authenticatedUser->company_id;

        if (!$company_id) {
            return $this->error_response([], 'We could not identify your company.');
        }

        $positions = Position::whereHas('company', function ($query) use ($company_id) {
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
