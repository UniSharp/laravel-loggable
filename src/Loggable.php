<?php

namespace Unisharp\Loggable;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Loggable
{
    protected $default_controller_namespace = 'App\\Http\\Controllers\\';
    protected $e;

    public function report($e)
    {
        $this->e = $e;

        if ($this->exceptionIsFormRequest()) {
            $this->writeDetailLogWithMessage('FormRequest failed');
        } elseif ($e instanceof TokenMismatchException) {
            $this->writeSimpleLog('Csrf token expired.');
        } elseif ($e instanceof ModelNotFoundException) {
            $this->writeSimpleLog('Model not found.');
        } elseif ($e instanceof NotFoundHttpException) {
            $this->writeSimpleLog('404 not found.');
        } elseif ($e instanceof HttpException) {
            $this->writeSimpleLog();
        } else {
            $this->writeSimpleLog();
            \Log::error($e);
        }
    }

    /*********************
     **    log types    **
     *********************/

    private function writeDetailLogWithMessage($msg, $with_input = null)
    {
        $user_trace = $this->getUserTrace();

        if ($with_input) {
            $user_trace[$msg] = request()->input();
        } else {
            array_push($user_trace, $msg);
        }

        $this->writeLog(json_encode([
            'user_id' => $this->getUserIdToDisplay(),
            'ip' => \Request::getClientIp(),
            'action_trace' => (object)$user_trace
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function writeSimpleLog($message = null)
    {
        $e = $this->e;
        $status_code = 0;
        if (method_exists($e, 'getStatusCode')) {
            $status_code = $e->getStatusCode();
        } else {
            $status_code = $e->getCode();
        }

        $full_message = implode(' | ', [
            $message ?: 'Status : ' . $status_code,
            '(' . \Request::method() . ') ' . \Request::fullUrl(),
            'User ID : ' . $this->getUserIdToDisplay(),
            'IP: ' . \Request::getClientIp()
        ]);

        $this->writeLog($full_message);
    }

    private function writeLog($log_message)
    {
        \Log::debug($log_message);
    }

    /**********************
     **  trace handling  **
     **********************/

    public function getUserTrace()
    {
        $user_trace = session($this->traceSessionKey());
        $arr_logs = [];

        $max_length_controller = $this->getMaxStrLength($user_trace, 0);
        $max_length_action = $this->getMaxStrLength($user_trace, 1);

        foreach ($user_trace as $trace) {
            $route_action = $this->getActionArray($trace);
            if ($route_action[0] == 'null' && $route_action[1] == 'null') {
                $msg = $trace['url'];
            } else {
                $controller = sprintf("%-{$max_length_controller}s", $route_action[0]);
                $action = sprintf("%-{$max_length_action}s", $route_action[1]);
                $msg = "Visited : {$controller} | Action : {$action}";
            }

            if ($trace['is_ajax']) {
                $msg .= " | Type : Ajax";
            }

            array_push($arr_logs, $msg);
        }

        return $arr_logs;
    }

    public function setUserTrace()
    {
        $max_trace_count = 6;
        $user_trace = session($this->traceSessionKey()) ?: [];

        array_push($user_trace, [
            'action' => \Route::currentRouteAction(),
            'is_ajax' => \Request::ajax(),
            'url' => \Request::fullUrl()
        ]);

        if (count($user_trace) > $max_trace_count) {
            $user_trace = array_slice($user_trace, -$max_trace_count);
        }

        session([$this->traceSessionKey() => $user_trace]);
    }

    private function traceSessionKey()
    {
        return 'loggable.user.' . $this->getUserIdToDisplay() . '.' . str_replace('.', '_', \Request::getClientIp());
    }

    /*********************
     **  miscellaneous  **
     *********************/

    private function exceptionIsFormRequest()
    {
        return $this->e instanceof HttpResponseException && str_contains($this->e->getFile(), 'FormRequest');
    }

    private function getUserIdToDisplay()
    {
        if (\Auth::check()) {
            $user_id = auth()->id();
        } else {
            $user_id = 'null';
        }

        return $user_id;
    }

    private function getMaxStrLength($user_trace, $key)
    {
        $max_str_length = 0;

        foreach ($user_trace as $trace) {
            $str_length = strlen($this->getActionArray($trace)[$key]);

            if ($str_length > $max_str_length) {
                $max_str_length = $str_length;
            }
        }

        return $max_str_length;
    }

    private function getActionArray($trace)
    {
        if (str_contains($trace['action'], '@')) {
            return explode('@', str_replace($this->default_controller_namespace, '', $trace['action']));
        } else {
            return ['null', 'null'];
        }
    }
}
