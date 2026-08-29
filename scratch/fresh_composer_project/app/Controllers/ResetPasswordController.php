<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\PasswordBroker;
use App\Models\User;

class ResetPasswordController
{
    public function show(Request $request): Response
    {
        $token = $request->query('token');
        return view('auth/reset-password', compact('token'));
    }

    public function reset(Request $request): Response
    {
        $data = $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $ok = PasswordBroker::reset(
            $data['email'],
            $data['token'],
            $data['password']
        );

        if (!$ok) {
            return back()->with('error', 'Invalid or expired reset link.');
        }

        return redirect('/login')->with('success', 'Password reset! Please log in.');
    }
}