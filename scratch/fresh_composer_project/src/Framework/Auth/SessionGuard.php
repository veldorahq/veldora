<?php

declare(strict_types=1);

namespace Veldora\Framework\Auth;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Session\Session;
use Veldora\Framework\Http\CookieJar;

class SessionGuard implements GuardInterface
{
    /**
     * The currently authenticated user.
     */
    protected ?Model $user = null;

    /**
     * Create a new SessionGuard instance.
     *
     * @param class-string<Model> $userModelClass
     */
    public function __construct(
        protected Session $session,
        protected string $userModelClass,
        protected CookieJar $cookieJar
    ) {
    }

    /**
     * Determine if the current user is authenticated.
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Determine if the current user is a guest.
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Model
    {
        if ($this->user !== null) {
            return $this->user;
        }

        // 1. Check Session
        $id = $this->session->get('_auth_user_id');
        if ($id !== null) {
            $userClass = $this->userModelClass;
            $user = $userClass::find($id);
            if ($user !== null) {
                $this->user = $user;
                return $this->user;
            }
        }

        // 2. Fallback to Remember Me Cookie
        $app = \Veldora\Framework\Foundation\Application::getInstance();
        $request = $app->has(\Veldora\Framework\Http\Request::class)
            ? $app->get(\Veldora\Framework\Http\Request::class)
            : null;

        if ($request !== null) {
            $rememberToken = $request->signedCookie('remember_web');
            if (is_string($rememberToken) && str_contains($rememberToken, '|')) {
                [$userId, $token] = explode('|', $rememberToken, 2);
                $userClass = $this->userModelClass;
                $user = $userClass::find($userId);

                if ($user !== null && is_string($user->remember_token) && hash_equals($user->remember_token, $token)) {
                    $this->login($user);
                    return $this->user;
                }
            }
        }

        return null;
    }

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id(): mixed
    {
        return $this->user() ? $this->user()->id : null;
    }

    /**
     * Log a user into the application.
     */
    public function login(Model $user, bool $remember = false): void
    {
        $this->session->put('_auth_user_id', $user->id);
        $this->user = $user;

        if ($remember) {
            $token = bin2hex(random_bytes(30));
            $user->remember_token = $token;
            $user->save();

            // Set signed cookie for 5 years
            $cookieValue = $user->id . '|' . $token;
            $this->cookieJar->queueSigned('remember_web', $cookieValue, 2628000);
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        if ($this->user() !== null) {
            $user = $this->user();
            $user->remember_token = null;
            $user->save();
        }

        $this->session->forget('_auth_user_id');
        $this->cookieJar->queueForget('remember_web');
        $this->user = null;
    }

    /**
     * Attempt to authenticate a user using credentials.
     *
     * @param array<string, mixed> $credentials
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';

        $userClass = $this->userModelClass;
        $userInstance = new $userClass();

        $row = $userInstance->query()->where('email', '=', $email)->first();
        if ($row === null) {
            return false;
        }

        $user = $userInstance->newFromBuilder($row);
        
        $hashed = $user->password ?? '';
        if (password_verify($password, $hashed)) {
            $this->login($user, $remember);
            return true;
        }

        return false;
    }

    /**
     * Determine if the authenticated user is an administrator.
     */
    public function isAdmin(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        return (bool) ($user->is_admin ?? false);
    }
}
