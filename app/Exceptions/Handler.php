<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (\Exception $e, $request) {

            if ($request->is('api/*')) {
                $id = $request->id??1;

                // Route not found
                if ($e instanceof NotFoundHttpException) {
                    return self::error(-32600, $id ?? "");
                } // Request method not allowed for this route
                elseif ($e instanceof MethodNotAllowedHttpException) {
                    return self::error(-32601, $id ?? "");
                } // No entry was found for this identifier.
                elseif ($e instanceof NotFoundExceptionInterface) {
                    return self::error(-32700, $id ?? "");
                } else {
                    return self::wrapper(500, $e->getMessage() . ". Line:" . $e->getLine() . ". File:" . pathinfo($e->getFile())['basename']);
                }
            }

            if ($e instanceof NotFoundHttpException) {
                return redirect('/');
            }
            if (auth()->guest()) {
                return redirect()->route('login');
            }
            return parent::render($request, $e);
        });
    }
    public static function error($code, $id = null)
    {
//        $error = Error::where('code', $code)->first();
//        if ($error !=null)
//            return self::wrapper($error->code, $error->en);
//        else
        return self::wrapper($code, "Undefined error");
    }

    private static function wrapper($code, $message, $id =null){

        $message = [
            "jsonrpc"   => "2.0",
            "status"    => false,
            "origin"    => "any.error",
            "error"     => [
                "code"      => $code,
                "message"   => $message ?? "Undefined Error"
            ],
            'host'      => [
                'name'      => config('app.name'),
                'time_stamp'=> date('Y-m-d H:i:s', time())
            ]
        ];

        return response()->json($message);
    }

}
