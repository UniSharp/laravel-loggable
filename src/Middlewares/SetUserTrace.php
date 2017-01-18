<?php

namespace Unisharp\Loggable\Middlewares;

use Closure;

class SetUserTrace
{
    public function handle($request, Closure $next)
    {
        \Loggable::setUserTrace();

        return $next($request);
    }
}
