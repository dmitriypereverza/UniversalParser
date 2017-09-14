<?php

namespace App\Http\Middleware;

use App\Models\Application;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;

class ApiAuth {
    public function handle($request, Closure $next) {
        $authToken = $request->bearerToken();

        try {
            $this->payloadIsValid(
                $payload = (array) JWT::decode($authToken, getenv('JWT_KEY'), ['HS256'])
            );
            $app = Application::where('key', '=', $payload['sub'])->firstOrFail();
        } catch (ExpiredException $e) {
            return response('token_expired', 401);
        } catch (\Throwable $e) {
            return response('token_invalid', 401);
        }

        if (! $app->is_active) {
            return response('app_inactive', 403);
        }

        return $next($request);
    }

    private function payloadIsValid($payload) {
        $validator = Validator::make($payload, [
            'iss' => 'required',
            'sub' => 'required',
        ]);

        if (!$validator->passes()) {
            throw new \InvalidArgumentException;
        }
    }
}
