<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                'description' => 'nullable|string',
                'address' => 'required|string',
                'image' => 'required', // Add validation rule for the Base64 image
            ]);

            if ($validator->fails()) {
                return $this->error_response2($validator->errors()->first());
            }

            // Ensure the user is authenticated
            if (!auth()->check()) {
                return $this->error_response2('User is not authenticated.');
            }

            $data = $request->only('latitude', 'longitude', 'description', 'address');
            $data['user_id'] = auth()->id();

            $truck_old = Track::where('user_id', $data['user_id'])->latest()->first();
            $data['type'] = is_null($truck_old) ? 0 : !$truck_old->type;

            $base64Image = $request->input('image');
            list($imageType, $imageData) = explode(";base64,", $base64Image);
            list(, $imageType) = explode(":", $imageType);
            list(, $imageExtension) = explode("/", $imageType);

            $imageExtension = strtolower($imageExtension); // Convert to lowercase for consistency

            // You can allow all image extensions or add additional extensions as needed
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif', 'bmp'];

            if (!in_array($imageExtension, $allowedExtensions)) {
                return $this->error_response2('Invalid image type. Allowed types are JPEG, PNG, JPG, GIF, BMP, etc.');
            }

            $binaryImage = base64_decode($imageData);

            if ($binaryImage === false) {
                return $this->error_response2('Invalid image data');
            }

            // Create a new folder in the 'public' directory based on the current month
            $currentMonthFolder = public_path('images/' . date('Y-m'));
            if (!file_exists($currentMonthFolder)) {
                mkdir($currentMonthFolder, 0755, true);
            }

            $imagePath = 'images/' . date('Y-m') . '/' . uniqid() . '.' . $imageExtension;
            $publicPath = public_path($imagePath);

            file_put_contents($publicPath, $binaryImage); // Save the image using file_put_contents

            $data['image'] = $imagePath;

            $result = Track::create($data);

            $imageUrl = asset($imagePath);
            $result['image_url'] = $imageUrl;

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

    public function lastSubmit(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $result = [
            'id' => null,
            'user_id' => null,
            'image' => null,
            'latitude' => null,
            'longitude' => null,
            'type' => true,
            'created_at' => null,
            'updated_at' => null,
            'description' => null,
            'role_id' => $user->roles->isNotEmpty() ? $user->roles->first()->id : null,
        ];

        $lastTrack = Track::where('user_id', $user->id)->latest()->first();

        if (!$lastTrack) {
            return $this->success_response($result);
        }

        if ($user->status === 'inactive') {
            $message = [
                'en' => 'Your company is inactive',
                'uz' => 'Sizning kompaniyangiz faol emas',
                'ru' => 'Ваша компания неактивна',
            ];
            return $this->error_response2($message);
        }

        $result = [
            'id' => $lastTrack->id,
            'user_id' => $lastTrack->user_id,
            'image' => $lastTrack->image,
            'latitude' => $lastTrack->latitude,
            'longitude' => $lastTrack->longitude,
            'type' => $lastTrack->type,
            'created_at' => $lastTrack->created_at,
            'updated_at' => $lastTrack->updated_at,
            'description' => $lastTrack->description,
            'role_id' => $user->roles->isNotEmpty() ? $user->roles->first()->id : null,
        ];

        return $this->success_response($result);
    }


    public function updateTruckData(Request $request, $truck_id)
    {
        if (auth()->user()->roles->first()->id !== 4) {
            return $this->error_response2('Unauthorized. You do not have the required role.');
        }
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'address' => 'required|string',
            'created_at' => 'required|date',
        ]);
        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }
        $truck = Track::find($truck_id);

        if (!$truck) {
            return $this->error_response2('Truck not found');
        }

        $data = $request->only('latitude', 'longitude', 'address', 'created_at');

        $truck->update($data);

        $message = ([
            'en' => 'Truck data has been updated.',
            'uz' => "Keldi-ketti ma'lumotlari yangilandi.",
            'ru' => 'Данные грузовика обновлены.',
        ]);

        return $this->success_response($truck, $message);

    }


    public function getUserTracksByUserId(Request $request, $user_id)
    {

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $tracks = Track::selectRaw('id, user_id, image, latitude, longitude, address, type, description,created_at, updated_at, DATE(created_at) as created_date')
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->where('user_id', $user_id) // Modified to use the $user_id parameter
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('created_date');

        $data = [];
        foreach ($tracks as $track) {
            $first = $track->first();
            $last = $track->last();
            $data[] = [
                "address" => $first->address,
                "image" => $first->image,
                "in_date" => $first->created_at,
                "out_date" => $last->type == 1 ? $last->created_at : '',
                "tracks" => $track,
            ];
        }
        $message = [
            'uz' => 'Muvaffaqqiyatli',
            'ru' => 'Успешно',
            'en' => 'Successful',
        ];

        return $this->success_response($data, $message);

    }

//    --------------------- test --------------------

    public function getUserTracks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $tracks = Track::selectRaw('id, user_id, image, latitude, longitude, address, description, type, created_at, updated_at, DATE(created_at) as created_date')
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedTracks = $tracks->groupBy('created_date');

        $data = [];
        foreach ($groupedTracks as $createdDate => $trackGroup) {
            $firstTrack = $trackGroup->last();
            $lastTrack = $trackGroup->where('type', 1)->first();

            $data[] = [
                "address" => $firstTrack->address,
                "image" => $firstTrack->image,
                "in_date" => $firstTrack->created_at,
                "out_date" => $lastTrack ? ($lastTrack->type == 1 ? $lastTrack->created_at : '') : '',
                "tracks" => $trackGroup,
            ];
        }

        $message = [
            'uz' => 'Muvaffaqqiyatli',
            'ru' => 'Успешно',
            'en' => 'Successful',
        ];

        return $this->success_response($data, $message);
    }


}

