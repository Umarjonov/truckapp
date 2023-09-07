<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackerController extends Controller
{
//    public function register(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'latitude' => 'required|numeric',
//            'longitude' => 'required|numeric',
//            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
//        ]);
//
//        if ($validator->fails()) return $this->error_response2($validator->errors()->first());
//
//        $data = $request->only('latitude', "longitude");
//        $data['user_id'] = auth()->id();
//        $truck_old = Track::where('user_id', $data['user_id'])->latest()->first();
//        $data['type'] = is_null($truck_old) ? 0 : !$truck_old->type;
//        $track = Track::create($data);
//        return $this->success_response($track, "success");
//    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->error_response2($validator->errors()->first());
        }

        $data = $request->only('latitude', 'longitude');
        $data['user_id'] = auth()->id();
        $truck_old = Track::where('user_id', $data['user_id'])->latest()->first();
        $data['type'] = is_null($truck_old) ? 0 : !$truck_old->type;
        $path = public_path('images/');
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move($path, $imageName);
        $data['image'] = $imageName;
        $track = Track::create($data);

        // Include the image URL or path in the response
        $track->image_url = asset('images/'.$track->image); // Assuming the 'public' disk is used

        return $this->success_response($track, 'success');
    }


}
