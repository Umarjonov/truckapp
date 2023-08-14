<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function error_response($message, $code = 400, $data = null)
    {
        $error = [
            "status" => false,
            "error" => [
                "code" => $code,
                "message" => $message
            ]
        ];

        if ($data != null) {
            $error['error']['data'] = $data;
        }
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

    public $secret = 1234;
}
