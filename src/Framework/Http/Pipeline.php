<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

use Closure;
use InvalidArgumentException;
use Veldora\Framework\Foundation\ContainerInterface;

class Pipeline
{
    /**
     * The object being passed through the pipeline.
     */
    protected mixed $passable;

    /**
     * The array of pipes (middlewares).
     *
     * @var array<mixed>
     */
    protected array $pipes = [];

    /**
     * Create a new Pipeline instance.
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * Set the object passed through the pipeline.
     */
    public function send(mixed $passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param array<mixed> $pipes
     */
    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     */
    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $destination
        );

        return $pipeline($this->passable);
    }

    /**
     * Get the closure that represents a slice of the pipeline.
     */
    protected function carry(): Closure
    {
        return function (Closure $stack, mixed $pipe) {
            return function (mixed $passable) use ($stack, $pipe) {
                if (is_string($pipe)) {
                    $pipeInstance = $this->container->get($pipe);
                    if ($pipeInstance instanceof MiddlewareInterface) {
                        return $pipeInstance->handle($passable, $stack);
                    }
                    if (is_callable([$pipeInstance, 'handle'])) {
                        /** @var callable $callable */
                        $callable = [$pipeInstance, 'handle'];
                        return $callable($passable, $stack);
                    }
                    throw new InvalidArgumentException("Middleware class [{$pipe}] must implement Veldora\\Framework\\Http\\MiddlewareInterface or define a handle method.");
                }

                if ($pipe instanceof Closure) {
                    return $pipe($passable, $stack);
                }

                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                }

                throw new InvalidArgumentException('Pipeline pipe is not callable or a valid middleware class name.');
            };
        };
    }
}
