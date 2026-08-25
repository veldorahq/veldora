<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
