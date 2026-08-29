<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;

class ProfileController
{
    public function show(): Response
    {
        $user = Auth::user();
        return view('auth/profile', compact('user'));
    }

    public function update(Request $request): Response
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'bio'   => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $user->name = $data['name'];
        $user->bio  = $data['bio'] ?? '';
        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request): Response
    {
        $data = $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = Auth::user();

        if (!password_verify($data['current_password'], $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->save();

        return back()->with('success', 'Password changed successfully!');
    }
}