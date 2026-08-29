<?php

declare(strict_types=1);

namespace Veldora\Framework\Auth;

use Veldora\Framework\Database\Model;

interface GuardInterface
{
    /**
     * Determine if the current user is authenticated.
     */
    public function check(): bool;

    /**
     * Determine if the current user is a guest.
     */
    public function guest(): bool;

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Model;

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id(): mixed;

    /**
     * Log a user into the application.
     */
    public function login(Model $user, bool $remember = false): void;

    /**
     * Log the user out of the application.
     */
    public function logout(): void;

    /**
     * Attempt to authenticate a user using credentials.
     *
     * @param array<string, mixed> $credentials
     */
    public function attempt(array $credentials, bool $remember = false): bool;

    /**
     * Determine if the authenticated user is an administrator.
     */
    public function isAdmin(): bool;
}
