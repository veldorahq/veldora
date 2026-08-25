<?php

declare(strict_types=1);

use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
