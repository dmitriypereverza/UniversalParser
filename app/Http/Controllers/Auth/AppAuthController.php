<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppAuthController extends Controller
{
    public function authenticateApp(Request $request)
    {
        $credentials = base64_decode(Str::substr($request->header('Authorization'), 6));

        try {
            list($appKey, $appSecret) = explode(':', $credentials);

            /** @var Application $app */
            $app = Application::whereKeyAndSecret($appKey, $appSecret)->firstOrFail();
        } catch (\Throwable $e) {
            return response('invalid_credentials', 400);
        }

        if (!$app->is_active) {
            return response('app_inactive', 403);
        }

        return response([
            'token_type' => 'Bearer',
            'access_token' => $this->generateAuthToken($app->key),
        ]);
    }

    public function generateAuthToken($key)
    {
        $jwt = JWT::encode([
            'iss' => getenv('JWT_ISS'),
            'sub' => $key,
            'iat' => time(),
            'exp' => time() + ((24 * 60 * 60) * 365), // 1 year
        ], getenv('JWT_KEY'));

        return $jwt;
    }
}
