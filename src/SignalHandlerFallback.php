<?php
namespace ShoppingFeed\Signal;

/**
 * Substitute handler when the extension is not loaded on the current system
 */
class SignalHandlerFallback implements SignalHandlerInterface
{
    public function handle(int $signal, $info = null): void
    {
    }

    public function append(callable $callback, array $options = []): SignalHandlerInterface
    {
        return $this;
    }

    public function prepend(callable $callback, array $options = []): SignalHandlerInterface
    {
        return $this;
    }

    public function clear($channels = []): SignalHandlerInterface
    {
        return $this;
    }

    public function isEmpty(): bool
    {
        return true;
    }
}
