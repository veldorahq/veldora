<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;

class LoginController
{
    public function show(): Response
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth/login');
    }

    public function login(Request $request): Response
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $remember = $request->input('remember') === 'on';

        if (Auth::attempt($data, $remember)) {
            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function logout(): Response
    {
        Auth::logout();
        return redirect('/login');
    }
}