<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackerController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|string', // Assuming the image is sent as a base64 string
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $data = $request->only('latitude', 'longitude', 'description');
        $data['user_id'] = auth()->id();
        $truck_old = Track::where('user_id', $data['user_id'])->latest()->first();
        $data['type'] = is_null($truck_old) ? 0 : !$truck_old->type;

        if ($request->has('image')) {
            $base64Image = $request->input('image');
            $imageData = base64_decode($base64Image);
            $imageName = uniqid() . '.png';
            $imagePath = 'your-image-directory/' . $imageName;
            file_put_contents($imagePath, $imageData);
            $data['image_path'] = $imagePath;

        }

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
        $latestTrack = Track::latest()->first();

        return response()->json([
            'track' => $latestTrack,
        ]);
    }

//    public function getDataBetweenDates(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'start_date' => 'required|date',
//            'end_date' => 'required|date',
//        ]);
//
//        if ($validator->fails()) {
//            throw ValidationException::withMessages($validator->errors()->all());
//        }
//
//        $validatedData = $validator->validated();
//
//        $startDate = $validatedData['start_date'];
//        $endDate = $validatedData['end_date'];
//
//        $tracks = Track::whereBetween('created_at', [$startDate, $endDate])
//            ->with('relationships') // Replace 'relationships' with actual relationships if needed
//            ->get();
//
//        return response()->json([
//            'tracks' => $tracks,
//        ]);
//    }


}

