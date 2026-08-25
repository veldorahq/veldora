<?php

declare(strict_types=1);

namespace App\Models;

use Veldora\Framework\Database\Model;

class Post extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'posts';
}
