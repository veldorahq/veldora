<?php

declare(strict_types=1);

namespace App\Models;

use Veldora\Framework\Database\Model;

class User extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users';

    /**
     * Determine if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return (bool) ($this->attributes['is_admin'] ?? false);
    }

    /**
     * Determine if the user has verified their email.
     */
    public function hasVerifiedEmail(): bool
    {
        return !empty($this->attributes['email_verified_at']);
    }

    /**
     * Hash a plain-text password.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
