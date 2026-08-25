<?php

declare(strict_types=1);

namespace App\Models;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\SoftDeletes;
use Veldora\Framework\Auth\MustVerifyEmail;

class User extends Model
{
    use SoftDeletes;
    use MustVerifyEmail;

    protected ?string $table = 'users';

    /** @var array<int,string> */
    protected array $hidden = ['password', 'remember_token', 'reset_token'];

    /** @var array<int,string> */
    protected array $fillable = ['name', 'email', 'password', 'bio', 'profile_photo'];

    /**
     * Hash the password when set via mass assignment.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT);
    }
}