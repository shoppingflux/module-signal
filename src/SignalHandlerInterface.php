<?php
namespace ShoppingFeed\Signal;

interface SignalHandlerInterface
{
    /**
     * Silently override the previous installed handler
     */
    public const PREV_REPLACE = 1;

    /**
     * Restore the previous handler if the handler stack goes empty
     */
    public const PREV_RESTORE = 2;

    /**
     * throw an exception when registering an handler on a already installed handler for the given signal
     */
    public const PREV_ERROR = 3;

    /**
     * Re-dispatch the signal to the prev handler once it has been treated by the handler.
     */
    public const PREV_RECALL = 4;

    /**
     * Method invoked by the pcntl_signal handler, which receive parameters described here
     * https://www.php.net/manual/fr/function.pcntl-signal.php
     *
     * @param int  $signal
     * @param null $info
     */
    public function handle(int $signal, $info = null): void;

    /**
     * Insert a new handler at the end of the callbacks stack
     *
     * @param callable $callback The callback to invoke once the signal is received
     * @param array    $options  Examples : ['once' => true, 'group' => 'test']
     *
     * @return SignalHandlerInterface
     */
    public function append(callable $callback, array $options = []): self;

    /**
     * Insert a new handler on the top of the callbacks stack
     *
     * @param callable $callback The callback to invoke once the signal is received
     * @param array    $options  Examples : ['once' => true, 'group' => 'test']
     *
     * @return SignalHandlerInterface
     */
    public function prepend(callable $callback, array $options = []): self;

    /**
     * Uninstall all handlers associated to the signal, or focused on particular groups
     *
     * @param string|string[] $groups Names for one or many groups
     *
     * @return SignalHandlerInterface
     */
    public function clear($groups = []): self;

    /**
     * Determine if the callback stack is empty or not
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
