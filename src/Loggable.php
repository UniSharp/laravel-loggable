<?php

namespace Unisharp\Loggable;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Exception\HttpResponseException;

class Loggable
{
    public function report($e)
    {
        $messages = '';
        if (\Auth::check()) {
            $messages = 'User ID : ' . auth()->id();
        }

        $status_code = 0;
        if (method_exists($e, 'getStatusCode')) {
            $status_code = $e->getStatusCode();
        } else {
            $status_code = $e->getCode();
        }

        $messages = $messages . " | ($status_code)"
            . ' URL: (' . \Request::method() . ') '
            . \Request::fullUrl()
            . ' IP: ' . \Request::getClientIp();

        if (self::exceptionIsFormRequest($e)) {
            setUserLog('FormRequest failed', true);
            $user_log = getUserLog();
            \Log::debug($user_log);
        } elseif ($e instanceof TokenMismatchException) {
            \Log::debug('User ID : ' . auth()->id() . ' | Csrf token expired.');
        // } elseif ($e instanceof ModelNotFoundException) {

        } else {
            \Log::error($e);
        }
    }

    private function exceptionIsFormRequest($exception)
    {
        return $exception instanceof HttpResponseException && str_contains($exception->getFile(), 'FormRequest');
    }
}
