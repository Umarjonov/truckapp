<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TrackerController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|string', // Add validation for the image field
        ]);
        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $data = $request->only('latitude', 'longitude', 'description', 'image');
        $data['user_id'] = auth()->id();
        $truck_old = Track::where('user_id', $data['user_id'])->latest()->first();
        $data['type'] = is_null($truck_old) ? 0 : !$truck_old->type;

        $folderPath = "uploads/";
        $base64Image = explode(";base64,", $request->image);
        $explodeImage = explode("image/", $base64Image[0]);
        $imageType = $explodeImage[1];
        $image_base64 = base64_decode($base64Image[1]);
        $file = $folderPath . uniqid() . '. ' . $imageType;

        file_put_contents($file, $image_base64);

        $track = Track::create($data);

        $message = ([
            'en' => 'Your information has been received.',
            'uz' => "Sizning ma'lumotlaringiz qabul qilindi.",
            'ru' => 'Ваши данные получены.',
        ]);
        return $this->success_response($track, $message);
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

