<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;

class UserFactory
{
    public static function make(array $attributes = []): User
    {
        return new User(array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ], $attributes));
    }
}
