<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TrackerController extends Controller
{
    public function register(Request $request)
    {
        $user_id = $request->user()->id;
        $lat = $request->input('latitude');
        $long = $request->input('longitude');
        $type = $request->input('type');

        

        // Save the entry
        $track = new Track();
        $track->user_id = $user_id;
        $track->latitude = $lat;
        $track->longitude = $long;
        $track->type = $type;
        $track->timestamps = now();
        $track->save();


        if ($type == 0) {
            return response()->json("User is here!");
        } elseif ($type == 1) {
            // Calculate the time difference between type 0 and type 1 entries
            $timeDifference = $this->calculateTimeDifference($user_id);
            return response()->json("Go home. Time since type 0: $timeDifference hours.");
        }
    }

    protected function calculateTimeDifference($user_id)
    {
        $type0Entry = Track::where('user_id', $user_id)
            ->where('type', 0)
            ->latest('created_at')
            ->first();

        $type1Entry = Track::where('user_id', $user_id)
            ->where('type', 1)
            ->latest('created_at')
            ->first();

        if ($type0Entry && $type1Entry) {
            $timeDifference = $type1Entry->created_at->diff($type0Entry->created_at);
            $hours = $timeDifference->h;
            $minutes = $timeDifference->i;
            return "$hours hours and $minutes minutes";
        }

        return "N/A";
    }

    // check o more than 2
    protected function hasConsecutiveSameType($user_id, $type)
    {
        $latestEntry = Track::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestEntry && $latestEntry->type === $type;
    }


}
