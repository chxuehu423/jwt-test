<?php

namespace App\Exceptions;

use App\Utils\JsonBase;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     * @param \Illuminate\Http\Request $request
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException){
            //return response(['error' => Arr::first(Arr::collapse($exception->errors()))], 400);
            return JsonBase::renderJsonWithFail($exception->getMessage(), [], 1, 1002, 400);
        }
        if ($exception instanceof UnauthorizedHttpException){
            //return response($exception->getMessage(), 401);
            return JsonBase::renderJsonWithFail($exception->getMessage(), [], 1, 1002, 401);
        }
        return parent::render($request, $exception);
    }
}
