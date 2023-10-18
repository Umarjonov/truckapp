<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function error_response($data, $uz, $ru = null, $en = null)
    {
        $error = [
            "status" => false,
            "result" => $data,
            "error" => [
                "code" => 400,
                "message" => [
                    "uz" => $uz,
                    "ru" => $ru ?? $uz,
                    "en" => $en ?? $uz,
                ]
            ]
        ];
        return response()->json($error, 400);
    }

    public function error_response2($data = null)
    {
        $error = [
            "status" => false,
            "error" => [
                "code" => 400,
                "message" => $data
            ]
        ];

        return response()->json($error, 400);
    }

    public function success_response($result, $message = null, $code = 200)
    {
        $response = [
            "status" => true,
            "result" => $result
        ];

        if ($message != null) {
            $response['message'] = $message;
        }
        return response()->json($response, $code);

    }

    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }

}
