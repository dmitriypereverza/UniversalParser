<?php

namespace App\Http\Middleware;

use Closure;

class IpMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!$this->isValidIp($request->ip()) || $request->getPort() != 80) {
            return response('access_denied', 403);
        }
        return $next($request);
    }

    private function isValidIp($ip)
    {
        return in_array($ip,  explode(';', env('ALLOW_CLIENT_IP')));
    }
}