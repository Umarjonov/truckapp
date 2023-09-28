<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
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
            'image' => 'required|string', // Add validation rule for the Base64 image
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

        // Generate the image URL
        $imageUrl = asset('storage/' . $imagePath);
        $result['image_url'] = $imageUrl; // Assign the corrected image URL here
        $message = ([
            'en' => 'Your information has been received.',
            'uz' => "Sizning ma'lumotlaringiz qabul qilindi.",
            'ru' => 'Ваши данные получены.',
        ]);

        return $this->success_response($result, $message);
    }

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

}

