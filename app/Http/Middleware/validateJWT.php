<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;

class validateJWT
{
	/**
	 * @return bool
	 */
    public static function isValid() {
        if(Users::JWTStructureVerify() && !Users::JWTExpired()) {
            return true;
        }
        return false;
    }

    public function handle($request, Closure $next) {
        if(!self::isValid()) {
            return \Response::json([
                'response' => [
                    'status' => 307,
                    'error' => [
                        'message' => 'Token not is valid!!'
                    ]
                ]
            ], 401);
        }
        return $next($request);
    }
}
