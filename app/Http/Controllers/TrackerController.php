<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TrackerController extends Controller
{

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|string',
                'longitude' => 'required|string',
                'description' => 'required|string',
                'address' => 'required|string',
                'image' => 'required', // Add validation rule for the Base64 image
            ]);
            dd($validator);
            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

            $data = $request->only('latitude', 'longitude', 'description', 'address');
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
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

//    last submit type

    public function lastsubmit()
    {
        try {
            $tracks = Track::latest()->first();

            // Fetch the user along with their roles
            $user = User::where('id', $tracks->user_id)->with('roles')->firstOrFail();

            $message = [
                'en' => 'History',
                'uz' => "Tarix",
                'ru' => 'История',
            ];

            // Create the result array with the track data and role_id
            $result = [
                'id' => $tracks->id,
                'user_id' => $tracks->user_id,
                'image' => $tracks->image,
                'latitude' => $tracks->latitude,
                'longitude' => $tracks->longitude,
                'type' => $tracks->type,
                'created_at' => $tracks->created_at,
                'updated_at' => $tracks->updated_at,
                'description' => $tracks->description,
                'role_id' => $user->roles->first()->id, // Assuming a user has only one role
            ];

            return $this->success_response($result, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


//    History for data

    public function getDataBetweenDates(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $tracks = Track::whereBetween('created_at', [$startDate, $endDate])->get();
            $message = ([
                'en' => 'History',
                'uz' => "Tarix",
                'ru' => 'История',
            ]);

            return $this->success_response($tracks, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserIdTracks($user_id)
    {
        try {
            $user = User::with(['tracks' => function ($query) {
                // Filter tracks for today
                $query->whereDate('created_at', Carbon::today());
            }])->find($user_id);

            if (!$user) {
                return $this->error_response([], 'User not found');
            }

            $message = [
                'uz' => 'Muvaffaqqiyatli',
                'ru' => 'Успешно',
                'en' => 'Successful',
            ];

            return $this->success_response($user, $message);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

//    Admin panel

    public function userTruckDaily(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

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
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateTruckData(Request $request, $truck_id)
    {
        try {
            // Check if the authenticated user has the required role (role_id equal to 2)
            if (auth()->user()->roles->first()->id !== 4) {
                return $this->error_response2('Unauthorized. You do not have the required role.');
            }
            $validator = Validator::make($request->all(), [
                'created_at' => 'required|date',
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }
            $truck = Track::find($truck_id);

            if (!$truck) {
                return $this->error_response2('Truck not found');
            }

            $data = $request->only('created_at');

            $truck->update($data);

            $message = ([
                'en' => 'Truck data has been updated.',
                'uz' => "Keldi-ketti ma'lumotlari yangilandi.",
                'ru' => 'Данные грузовика обновлены.',
            ]);

            return $this->success_response($truck, $message);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}

