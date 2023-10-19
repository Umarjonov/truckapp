<?php

namespace App\Http\Controllers;

use App\Models\Notcame;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NotcameController extends Controller
{
    public function notCame(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|string',
                'longitude' => 'required|string',
                'description' => 'required|string',
                'address' => 'required|string',
                'image' => 'required', // Add validation rule for the Base64 image
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

            $data = $request->only('latitude', 'longitude', 'description', 'address');
            $data['user_id'] = auth()->id();
            $base64Image = $request->input('image');
            $binaryImage = base64_decode($base64Image);

            $imagePath = 'images/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imagePath, $binaryImage);
            $data['image'] = $imagePath;

            $result = Notcame::create($data);

            $imageUrl = asset('storage/' . $imagePath);
            $result['image_url'] = $imageUrl; // Assign the corrected image URL here
            $message = ([
                'en' => 'Your information has been received.',
                'uz' => "Sizning ma'lumotlaringiz qabul qilindi.",
                'ru' => 'Ваши данные получены.',
            ]);

            return $this->success_response($result, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
