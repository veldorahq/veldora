<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
