<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Config;

class VerifyJWTToken {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        try {
            Config::set('auth.model', \App\Users::class);
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                            'status' => 3,
                            'message' => 'Failed to validating token.'
                ], 400);
            } else if ($user->deleted != 1) {
                return response()->json([
                            'status' => 3,
                            'message' => 'User is no more available. Please contact admin.'
                ], 404);
            }
        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                            'status' => 3,
                            'message' => 'Token Expired.'
                ], $e->getStatusCode());
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                            'status' => 3,
                            'message' => 'Invalid Token.'
                ], $e->getStatusCode());
            } else {
                return response()->json([
                            'status' => 3,
                            'message' => $e->getMessage()
                ], $e->getStatusCode());
            }
        }
        return $next($request);
    }

}
