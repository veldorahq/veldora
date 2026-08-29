<?php

declare(strict_types=1);

namespace Veldora\Framework\Auth;

/**
 * Trait MustVerifyEmail
 *
 * Implements email verification helper methods for User models.
 */
trait MustVerifyEmail
{
    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return !empty($this->attributes['email_verified_at']);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        $this->attributes['email_verified_at'] = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return (string) ($this->attributes['email'] ?? '');
    }
}
