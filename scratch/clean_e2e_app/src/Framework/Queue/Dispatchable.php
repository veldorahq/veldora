<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

trait Dispatchable
{
    /**
     * Dispatch the job with the given arguments.
     */
    public static function dispatch(mixed ...$args): PendingDispatch
    {
        return new PendingDispatch(new static(...$args));
    }

    /**
     * Dispatch the job if the given truth value is true.
     */
    public static function dispatchIf(bool $boolean, mixed ...$args): ?PendingDispatch
    {
        return $boolean ? static::dispatch(...$args) : null;
    }

    /**
     * Dispatch the job unless the given truth value is true.
     */
    public static function dispatchUnless(bool $boolean, mixed ...$args): ?PendingDispatch
    {
        return !$boolean ? static::dispatch(...$args) : null;
    }
}
