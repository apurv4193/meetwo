<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Input;
use Redirect;

class LoginController extends Controller {

    public function login() {
        if (Auth::user()) {
            return Redirect::to("/admin/dashboard");
        }
        return view('Admin.Login');
    }

    public function loginCheck() {
        $email = e(Input::get('email'));
        $password = e(Input::get('password'));
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            return Redirect::to("/admin/dashboard");
        }

        return Redirect::back()
                        ->withInput()
                        ->withErrors(trans('validation.invalidcombo'));
    }

    public function getLogout() {
        Auth::logout();

        return Redirect::to('/admin');
    }

}

?>
