<?php

declare(strict_types=1);

namespace Veldora\Framework\Auth;

use DateTimeImmutable;
use Veldora\Framework\Database\DB;
use Veldora\Framework\Support\Str;

/**
 * Class PasswordBroker
 *
 * Manages secure generation, validation, and deletion of password reset tokens.
 * Can be used as an instance or via convenient static methods.
 */
class PasswordBroker
{
    /**
     * Create a new password broker instance.
     *
     * @param string $table The password resets table name
     * @param int $expires The token expiration time in minutes (default 60)
     */
    public function __construct(
        protected string $table = 'password_resets',
        protected int $expires = 60
    ) {
    }

    /**
     * Create a new password reset token for the given user email.
     */
    public function makeToken(string $email): string
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        // Check if password_resets table exists, if not create it
        try {
            DB::statement("CREATE TABLE IF NOT EXISTS {$this->table} (email TEXT, token TEXT, created_at TEXT)");
        } catch (\Throwable $e) {
            // Ignore if already exists
        }

        // Delete existing tokens for this email
        try {
            DB::table($this->table)->where('email', '=', $email)->delete();
        } catch (\Throwable $e) {
            // Table might not exist yet
        }

        // Insert new token record
        DB::table($this->table)->insert([
            'email'      => $email,
            'token'      => $hashedToken,
            'created_at' => $now,
        ]);

        return $token;
    }

    /**
     * Validate the given password reset token for an email address.
     */
    public function checkToken(string $email, string $token): bool
    {
        try {
            $record = DB::table($this->table)->where('email', '=', $email)->first();
            if (!$record) {
                return false;
            }

            $hashedToken = hash('sha256', $token);
            if (!hash_equals($record['token'], $hashedToken)) {
                return false;
            }

            // Check expiration
            $createdAt = strtotime((string) $record['created_at']);
            if ($createdAt === false || (time() - $createdAt) > ($this->expires * 60)) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Delete the password reset token for the given email.
     */
    public function deleteToken(string $email): void
    {
        try {
            DB::table($this->table)->where('email', '=', $email)->delete();
        } catch (\Throwable $e) {
            // Table may not exist
        }
    }

    // ── Static Helper Methods ───────────────────────────────────────────────

    public static function createToken(string $email): string
    {
        return (new static())->makeToken($email);
    }

    public static function validateToken(string $email, string $token): bool
    {
        return (new static())->checkToken($email, $token);
    }

    public static function sendResetLink(string $email): string
    {
        $token = static::createToken($email);
        // Here a reset email would be queued or sent
        return $token;
    }

    public static function reset(string $email, string $token, string $password): bool
    {
        if (!static::validateToken($email, $token)) {
            return false;
        }

        // Update user password
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            DB::table('users')->where('email', '=', $email)->update([
                'password' => $hashedPassword,
            ]);

            (new static())->deleteToken($email);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
