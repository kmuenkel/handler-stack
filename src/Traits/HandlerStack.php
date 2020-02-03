<?php

namespace HandlerStack\Traits;

use InvalidArgumentException;

/**
 * Trait HandlerStack
 * @package App\Traits
 */
trait HandlerStack
{
    /**
     * @var callable[]
     */
    protected $handlers = [];

    /**
     * @var callable|null
     */
    protected $callback = null;

    /**
     * @param callable|null $callback
     * @return $this
     */
    public function setCallback(callable $callback = null)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @param callable $closure
     * @return $this
     */
    public function pushHandler(callable $closure)
    {
        $this->handlers[] = $closure;

        return $this;
    }

    /**
     * @return callable[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param callable[] $handlers
     * @return $this
     */
    public function setHandlers(array $handlers = [])
    {
        foreach ($handlers as $index => $handler) {
            if (!is_callable($handler)) {
                throw new InvalidArgumentException("Handler $index must be callable.");
            }
        }

        $this->handlers = $handlers;

        return $this;
    }

    /**
     * @param mixed $data
     * @param callable|null $final
     * @param array $extra
     * @return mixed
     */
    protected function handle($data, callable $final = null, array $extra = [])
    {
        $final = $final ?: function ($data) {
            return $data;
        };

        $callback = $this->callback ?: function (callable $current) {
            //
        };

        $wrapper = function (callable $previous, callable $current) use ($extra, $callback) {
            $next = function ($data, ...$args) use ($previous, $current, $extra, $callback) {
                $callback($current);

                $args = array_merge($args, $extra);

                return $current($data, $previous, ...$args);
            };

            return $next;
        };

        $handler = array_reduce(array_reverse($this->handlers), $wrapper, $final);

        $data = $handler($data);

        return $data;
    }
}
