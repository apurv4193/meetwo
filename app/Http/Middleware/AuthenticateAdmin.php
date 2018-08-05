<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Guard;
use Closure;
use Auth;

class AuthenticateAdmin {

    protected $auth;

    public function __construct(Guard $auth) {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next) {

        if (Auth::user()) {
            
        } else {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return view('Admin.Login');
            }
        }


        return $next($request);
    }

}
