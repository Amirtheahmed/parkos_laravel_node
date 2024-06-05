<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

trait ApiResponse
{
    protected string $resourceItem;
    protected string $resourceCollection;

    protected function responseWithSuccess(array|string $message = '', $data = [], $code = 200): JsonResponse
    {
        $message = !is_array($message) ? $message : $message['message'] ?? '';

        return response()->json([
            'type'    => "success",
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    function capitalizeFirstParagraph(string $paragraph): string
    {
        // Split the paragraph into sentences
        $sentences = preg_split('/(?<=[.?!])\s+/', $paragraph);

        // Capitalize the first letter of each sentence and convert the rest to lowercase
        $capitalizedSentences = array_map(function ($sentence) {
            $firstLetter    = mb_strtoupper(mb_substr($sentence, 0, 1));
            $restOfSentence = mb_strtolower(mb_substr($sentence, 1));

            return $firstLetter . $restOfSentence;
        }, $sentences);

        // Join the sentences back into a paragraph
        return implode(' ', $capitalizedSentences);
    }

    protected function getTimestampInMillisecond(): int
    {
        return intdiv((int)now()->format('Uu'), 1000);
    }

    protected function responseWithPending(array|string $message = '', $data = [], $code = 200): JsonResponse
    {
        $message = !is_array($message) ? $message : $message['message'] ?? '';

        return response()->json([
            'type'    => "pending",
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data, // initiator_id, action_id, started_at, resource_type, environment
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithError(array|string $message = '',
                                                      $data = [],
                                                      $code = 400,
                                                      $errorCode = null): JsonResponse
    {
        $message = !is_array($message) ? $message : $message['message'] ?? '';

        return response()->json([
            'type'      => "error",
            'errorCode' => $errorCode,
            'message'   => $this->capitalizeFirstParagraph($message),
            'data'      => $data,
            'meta'      => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithWarning(array|string $message = '',
                                                        $data = [],
                                                        $code = 400): JsonResponse
    {
        $message = !is_array($message) ? $message : $message['message'] ?? '';

        return response()->json([
            'type'    => "warning",
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithInfo(array|string $message = '',
                                                     $data = [],
                                                     $code = 400): JsonResponse
    {
        $message = !is_array($message) ? $message : $message['message'] ?? '';

        return response()->json([
            'type'    => "info",
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithRedirect($redirectUrl, $data = [], $code = 302): JsonResponse
    {
        return response()->json([
            'type'        => "redirect",
            'redirectUrl' => $redirectUrl,
            'data'        => $data,
            'meta'        => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithValidationErrors($validationErrors,
                                                    $message = 'Validation Error',
                                                    $code = 422): JsonResponse
    {
        return response()->json([
            'type'    => "validation_error",
            'message' => $this->capitalizeFirstParagraph($message),
            'errors'  => $validationErrors,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithUnauthorized($message = 'Unauthorized', $data = [], $code = 401): JsonResponse
    {
        return response()->json([
            'type'    => "unauthorized",
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithNotFound($message = 'Not Found', $data = [], $code = 404): JsonResponse
    {
        return response()->json([
            'type'    => "not_found",
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithMethodNotAllowed($message = 'Method Not Allowed',
                                                    $data = [],
                                                    $code = 405): JsonResponse
    {
        return response()->json([
            'type'    => 'method_not_allowed',
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithServiceUnavailable($message = 'Service Unavailable',
                                                      $data = [],
                                                      $code = 503): JsonResponse
    {
        return response()->json([
            'type'    => 'service_unavailable',
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithBadRequest($message = 'Bad Request', $data = [], $code = 400): JsonResponse
    {
        return response()->json([
            'type'    => 'bad_request',
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function responseWithTooManyRequests($message = 'Too Many Requests',
                                                   $data = [],
                                                   $code = 429): JsonResponse
    {
        return response()->json([
            'type'    => 'too_many_requests',
            'message' => $this->capitalizeFirstParagraph($message),
            'data'    => $data,
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $code);
    }

    protected function respondWithCustomData($data, $status = 200, $message = ''): JsonResponse
    {
        return new JsonResponse([
            'type'    => "success",
            'data'    => $data,
            'message' => $this->capitalizeFirstParagraph($message),
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], $status);
    }

    protected function respondWithNoContent(): JsonResponse
    {
        return new JsonResponse([
            'type'    => "success",
            'data'    => null,
            'message' => '',
            'meta'    => ['timestamp' => $this->getTimestampInMillisecond()],
        ], Response::HTTP_NO_CONTENT);
    }

    protected function respondWithCollection(LengthAwarePaginator|Collection $collection,
        string $message = ''
    )
    {
        return (new $this->resourceCollection($collection))->additional(
            [
                'type'    => "success",
                'message' => $this->capitalizeFirstParagraph($message),
                'meta'    => ['timestamp' => $this->getTimestampInMillisecond()]
            ]
        );
    }

    protected function respondWithItem(Model|array $item, string $message = '')
    {
        return (new $this->resourceItem($item))->additional(
            [
                'type'    => "success",
                'message' => $this->capitalizeFirstParagraph($message),
                'meta'    => ['timestamp' => $this->getTimestampInMillisecond()]
            ]
        );
    }
}
