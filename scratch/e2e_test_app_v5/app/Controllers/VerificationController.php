<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;
use App\Models\User;

class VerificationController
{
    public function notice(): Response
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }
        return view('auth/email-verify');
    }

    public function verify(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect('/dashboard')->with('success', 'Email verified!');
    }

    public function resend(): Response
    {
        // In a real app, re-send the verification email here.
        return back()->with('success', 'Verification link resent!');
    }
}