<?php
namespace ShoppingFeed\Signal;

class GroupHandler
{
    /**
     * @var string The name associated to the current instance
     */
    private $name;

    /**
     * The signal handler management mode
     *
     * @var int
     */
    private $mode;

    /**
     * @var SignalHandler[]|\ArrayObject
     */
    private $signals;

    /**
     * @param string                             $name
     * @param array|\ArrayObject|SignalHandler[] $signals
     * @param int                                $mode
     *
     * @return GroupHandler
     */
    public static function withSignals(
        string $name = '',
        $signals = [],
        int $mode = SignalHandlerInterface::PREV_REPLACE): self
    {
        if (! $signals instanceof \ArrayObject) {
            $signals = new \ArrayObject($signals);
        }

        $instance          = new self($name, $mode);
        $instance->signals = $signals;

        return $instance;
    }

    public function __construct(string $name = '', int $mode = SignalHandlerInterface::PREV_REPLACE)
    {
        $this->name    = $name;
        $this->mode    = $mode;
        $this->signals = new \ArrayObject();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Add a new callback at the end of the stack to handle signal(s)
     *
     * @param int|int[] $signals  A single or multiples signals at once
     * @param callable  $callback A callback to attach on the given signal(s)
     *
     * @return $this
     */
    public function append($signals, callable $callback): self
    {
        foreach ($this->getSignals($signals) as $stack) {
            $stack->append($callback, ['group' => $this->name]);
        }

        return $this;
    }

    /**
     * Add a new callback at the beginning of the stack to handle signal(s)
     *
     * @param int|int[] $signals  A single or multiples signals at once
     * @param callable  $callback A callback to attach on the given signal(s)
     *
     * @return $this
     */
    public function prepend($signals, callable $callback): self
    {
        foreach ($this->getSignals($signals) as $handler) {
            $handler->prepend($callback, ['group' => $this->name]);
        }

        return $this;
    }

    /**
     * @param int|int[] $signals
     */
    public function handle($signals): void
    {
        foreach ((array) $signals as $signal) {
            if (isset($this->signals[$signal])) {
                $this->signals[$signal]->handle($signal);
            }
        }
    }

    /**
     * Clear all or specified signal(s) callbacks, and restore default handler on those
     *
     * @param int|int[] $signals  A single or multiples signals at once
     *
     * @return $this
     */
    public function clear($signals = []): self
    {
        foreach ($this->getSignals($signals) as $signal => $handler) {
            $handler->clear($this->name);
            if ($handler->isEmpty()) {
                unset($this->signals[$signal]);
            }
        }

        return $this;
    }

    /**
     * @param int|int[] $signals
     *
     * @return iterable|SignalHandler[]
     */
    private function getSignals($signals): iterable
    {
        if (! $signals) {
            $signals = array_keys($this->signals->getArrayCopy());
        }

        foreach ((array) $signals as $signal) {
            if (! isset($this->signals[$signal])) {
                $this->signals[$signal] = SignalHandler::factory($signal, $this->mode);
            }

            yield $signal => $this->signals[$signal];
        }
    }
}
