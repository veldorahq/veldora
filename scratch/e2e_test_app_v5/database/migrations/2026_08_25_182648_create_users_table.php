<?php

declare(strict_types=1);

use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('bio', 500)->nullable();
            $table->string('reset_token', 100)->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}