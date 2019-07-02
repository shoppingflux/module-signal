<?php
namespace ShoppingFeed\Signal;

class GlobalHandler
{
    /**
     * @var SignalHandler[]
     */
    private $signals;

    /**
     * @var GroupHandler[]
     */
    private $groups;

    /**
     * @var int
     */
    private $mode;

    public function __construct(int $mode = SignalHandlerInterface::PREV_REPLACE)
    {
        $this->signals = new \ArrayObject();
        $this->groups  = [];
        $this->mode    = $mode;
    }

    /**
     * Multiton entry point, that allow to segregate handlers management per channel
     *
     * @param string $name
     * @param int    $mode
     *
     * @return GroupHandler
     */
    public function group(string $name = '', int $mode = null): GroupHandler
    {
        if (! isset($this->groups[$name])) {
            $this->groups[$name] = GroupHandler::withSignals($name, $this->signals, $mode ?? $this->mode);
        }

        return $this->groups[$name];
    }

    /**
     * Clear then remove channel from managed instances, if present
     *
     * @param string|string[] $names
     *
     * @return $this
     */
    public function clearGroup($names): self
    {
        foreach ((array) $names as $name) {
            if (isset($this->groups[$name])) {
                $this->groups[$name]->clear();
                unset($this->groups[$name]);
            }
        }

        return $this;
    }

    public function signal(int $signal, int $mode = null): SignalHandlerInterface
    {
        if (! isset($this->signals[$signal])) {
            $this->signals[$signal] = SignalHandler::factory($signal, $mode ?? $this->mode);
        }

        return $this->signals[$signal];
    }

    /**
     * Clear all managed instances, uninstall handlers
     * and remove internal references if no specific signals are provided
     *
     * @param int|int[] $signals
     *
     * @return $this
     */
    public function clearSignal($signals): self
    {
        foreach ((array) $signals as $signal) {
            if (isset($this->signals[$signal])) {
                $this->signals[$signal]->clear();
                unset($this->signals[$signal]);
            }
        }

        return $this;
    }

    public function clear(): self
    {
        $this->clearGroup(array_keys($this->groups));
        $this->groups = [];
        $this->clearSignal(array_keys($this->signals->getArrayCopy()));
        $this->signals->exchangeArray([]);

        return $this;
    }
}
