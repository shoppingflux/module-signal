<?php
namespace ShoppingFeed\Signal;

class SignalHandler implements SignalHandlerInterface
{
    public static function factory(int $signal, int $flags = self::PREV_REPLACE): SignalHandlerInterface
    {
        if (\function_exists('pcntl_signal')) {
            return new self($signal, $flags);
        }

        return new SignalHandlerFallback();
    }

    /**
     * @var callable[][]
     */
    private $subscribers;

    /**
     * The previous installed handler
     *
     * @var callable|int INT for SIG_DFL or SIG_IGN, installed handler otherwise
     */
    private $previous;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var int
     */
    private $signal;

    public function __construct(int $signal, int $flags = self::PREV_REPLACE)
    {
        // do not question for current state, it's far slowest
        pcntl_async_signals(true);

        $this->flags       = $flags;
        $this->signal      = $signal;
        $this->subscribers = new \SplDoublyLinkedList();
        $this->previous    = pcntl_signal_get_handler($signal);
    }

    public function append(callable $callback, array $options = []): SignalHandlerInterface
    {
        if ($this->isEmpty()) {
            $this->install();
        }

        $this->subscribers->push(
            $this->createSubscriber($callback, $options)
        );

        return $this;
    }

    public function prepend(callable $callback, array $options = []): SignalHandlerInterface
    {
        if ($this->isEmpty()) {
            $this->install();
        }

        $this->subscribers->unshift(
            $this->createSubscriber($callback, $options)
        );

        return $this;
    }

    public function clear($groups = []): SignalHandlerInterface
    {
        $groups  = (array) $groups;
        $indexes = [];

        foreach ($this->subscribers as $key => $subscriber) {
            if (! $groups || in_array($subscriber->group, $groups, true)) {
                $indexes[] = $key;
            }
        }

        // Remove elements from bottom to top, as the list is re-indexed every time an element is removed
        sort($indexes);
        while (null !== ($index = array_pop($indexes))) {
            unset($this->subscribers[$index]);
        }

        if ($this->isEmpty()) {
            $this->restore();
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->subscribers->isEmpty();
    }

    public function handle(int $signal, $info = null): void
    {
        foreach ($this->subscribers as $key => $subscriber) {
            ($subscriber->handler)($signal, $info);
            if (true === $subscriber->once) {
                unset($this->subscribers[$key]);
            }
        }

        if ($this->isEmpty()) {
            $this->restore($signal);
        }
    }

    private function restore(int $signal = null): void
    {
        if ($this->flags & self::PREV_RESTORE) {
            pcntl_signal($this->signal, $this->previous);
        }
        if (($this->flags & self::PREV_RECALL) && function_exists('posix_kill')) {
            posix_kill(posix_getpid(), $signal);
        }
    }

    private function install(): void
    {
        if ($this->previous && self::PREV_ERROR === $this->flags) {
            throw new Exception\RuntimeException(
                sprintf('An handler is already installed for signal %d, aborting.', $this->signal)
            );
        }

        pcntl_signal($this->signal, [$this, 'handle']);
    }

    private function createSubscriber(callable $callback, array $options): \stdClass
    {
        $options = array_replace(['group' => '', 'once' => false], $options);

        $options['handler'] = $callback;

        return (object) $options;
    }
}
