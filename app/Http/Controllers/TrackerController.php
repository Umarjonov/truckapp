<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TrackerController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'description' => 'required|string',
            'image' => 'required', // Add validation rule for the Base64 image
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $data = $request->only('latitude', 'longitude', 'description');
        $data['user_id'] = auth()->id();
        $truck_old = Track::where('user_id', $data['user_id'])->latest()->first();
        $data['type'] = is_null($truck_old) ? 0 : !$truck_old->type;

        $base64Image = $request->input('image');
        $binaryImage = base64_decode($base64Image);

        $imagePath = 'images/' . uniqid() . '.jpg';
        Storage::disk('public')->put($imagePath, $binaryImage);
        $data['image'] = $imagePath;

        $result = Track::create($data);

        $imageUrl = asset('storage/' . $imagePath);
        $result['image_url'] = $imageUrl; // Assign the corrected image URL here
        $message = ([
            'en' => 'Your information has been received.',
            'uz' => "Sizning ma'lumotlaringiz qabul qilindi.",
            'ru' => 'Ваши данные получены.',
        ]);

        return $this->success_response($result, $message);
    }

//    last submit type

    public function lastsubmit()
    {
        $tracks = Track::latest()->first();

        $message = ([
            'en' => 'History',
            'uz' => "Tarix",
            'ru' => 'История',
        ]);

        return $this->success_response($tracks, $message);
    }

//    History for data

    public function getDataBetweenDates(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $tracks = Track::whereBetween('created_at', [$startDate, $endDate])->get();
        $message = ([
            'en' => 'History',
            'uz' => "Tarix",
            'ru' => 'История',
        ]);

        return $this->success_response($tracks, $message);
    }

//    Admin panel

    public function getUserTracks(Request $request)
    {
        // Get the start date and end date from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Retrieve users with their tracks within the date range
        $users = User::with(['tracks' => function ($query) use ($startDate, $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }])->get();

        if ($users->isEmpty()) {
            return $this->error_response([], 'Users not found');
        }

        // Remove the user_id from each user object
        $users->each(function ($user) {
            unset($user->user_id);
        });

        $message = [
            'uz' => 'Muvaffaqqiyatli',
            'ru' => 'Успешно',
            'en' => 'Successful',
        ];

        return $this->success_response($users, $message);
    }
}

