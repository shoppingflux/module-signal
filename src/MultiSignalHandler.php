<?php
namespace ShoppingFeed\Signal;

class MultiSignalHandler implements SignalHandlerInterface
{
    /**
     * @var SignalHandlerInterface[]
     */
    private $signals;

    public function __construct(iterable $signals)
    {
        $this->signals = $signals;
    }

    public function handle(int $signo, $info = null): void
    {
        foreach ($this->signals as $signal => $handler) {
            if ($signal === $signo) {
                $handler->handle($signo, $info);
            }
        }
    }

    public function append(callable $callback, array $options = []): SignalHandlerInterface
    {
        foreach ($this->signals as $signal) {
            $signal->append($callback, $options);
        }

        return $this;
    }

    public function prepend(callable $callback, array $options = []): SignalHandlerInterface
    {
        foreach ($this->signals as $signal) {
            $signal->prepend($callback, $options);
        }

        return $this;
    }

    public function clear($groups = []): SignalHandlerInterface
    {
        foreach ($this->signals as $signal) {
            $signal->clear($groups);
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        $signals = $this->signals;

        return count($signals) === count(array_filter($signals, function (SignalHandlerInterface $handler) {
            return $handler->isEmpty();
        }));
    }
}
