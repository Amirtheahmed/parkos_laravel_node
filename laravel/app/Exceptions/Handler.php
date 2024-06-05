<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use App\Traits\ApiResponse;

class Handler extends ExceptionHandler
{
    use ApiResponse;

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
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response|ResponseAlias
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Throwable $e): JsonResponse
    {
        $status  = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;
        $message = $e->getMessage();

        $handlers = [
            AuthenticationException::class       => fn() => $this->responseWithUnauthorized($message),
            AuthorizationException::class        => fn() => $this->responseWithUnauthorized($message),
            MethodNotAllowedHttpException::class => fn() => $this->responseWithMethodNotAllowed($message),
            ModelNotFoundException::class        => fn() => $this->responseWithNotFound(
                'The requested resource was not found'
            ),
            NotFoundHttpException::class         => fn() => $this->responseWithNotFound($message),
            QueryException::class                => fn() => $this->responseWithBadRequest('Internal error'),
            ThrottleRequestsException::class     => fn() => $this->responseWithTooManyRequests($message),
            ValidationException::class           => fn() => $this->responseWithValidationErrors(
                $e->validator->getMessageBag()->toArray(),
                $message
            ),
        ];

        foreach ($handlers as $exception => $handler) {
            if ($e instanceof $exception) {
                return $handler();
            }
        }

        return $this->responseWithError($message, null, $status);
    }
}
