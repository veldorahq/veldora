<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\PasswordBroker;
use App\Models\User;

class ForgotPasswordController
{
    public function show(): Response
    {
        return view('auth/forgot-password');
    }

    public function send(Request $request): Response
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        /** @var User|null $user */
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            // Don't reveal if e-mail exists — show same message
            return back()->with('success', 'If that email exists, a reset link has been sent.');
        }

        PasswordBroker::sendResetLink($user->email);

        return back()->with('success', 'Password reset link sent! Check your inbox.');
    }
}