<?php

namespace ShoppingFeed\Signal;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultiSignalHandlerTest extends TestCase
{
    private MultiSignalHandler $instance;

    private SignalHandlerInterface|MockObject $signal1;

    private SignalHandlerInterface|MockObject $signal2;

    public function setUp(): void
    {
        $this->signal1  = $this->createMock(SignalHandlerInterface::class);
        $this->signal2  = $this->createMock(SignalHandlerInterface::class);
        $this->instance = new MultiSignalHandler([1 => $this->signal1, 2 => $this->signal2]);
    }

    public function testHandleSignalForwardToSignalThatHandleSigno(): void
    {
        $this->signal2
            ->expects($this->once())
            ->method('handle')
            ->with(2, 'test');

        $this->signal1
            ->expects($this->never())
            ->method('handle');

        $this->instance->handle(2, 'test');
    }

    public function testHandleSignalForwardToNoSignal(): void
    {
        $this->signal1
            ->expects($this->never())
            ->method('handle');

        $this->signal2
            ->expects($this->never())
            ->method('handle');

        $this->instance->handle(3, 'test');
    }

    public function testAppendAddNewCallabackToAllSignals(): void
    {
        $callback = 'is_string';
        $options  = ['once' => 'true'];

        $this->signal1
            ->expects($this->once())
            ->method('append')
            ->with($callback, $options);

        $this->signal2
            ->expects($this->once())
            ->method('append')
            ->with($callback, $options);

        $this->instance->append($callback, $options);
    }

    public function testPrependAddNewCallabackToAllSignals(): void
    {
        $callback = 'is_string';
        $options  = ['once' => 'true'];

        $this->signal1
            ->expects($this->once())
            ->method('prepend')
            ->with($callback, $options);

        $this->signal2
            ->expects($this->once())
            ->method('prepend')
            ->with($callback, $options);

        $this->instance->prepend($callback, $options);
    }

    public function testClearIsInvokedOnAllSignals(): void
    {
        $groups = ['test'];

        $this->signal1
            ->expects($this->once())
            ->method('clear')
            ->with($groups);

        $this->signal2
            ->expects($this->once())
            ->method('clear')
            ->with($groups);

        $this->instance->clear($groups);
    }

    public function testIsEmptyExpectsAllSignalAreEmpty(): void
    {
        $this->signal1
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $this->signal2
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $this->assertTrue($this->instance->isEmpty());
    }

    public function testIsNotEmptyWhenOneSignalIsEmpty(): void
    {
        $this->signal1
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $this->signal2
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertFalse($this->instance->isEmpty());
    }

    public function testIsNotEmptyWhenAllSignalsAreNotEmpty(): void
    {
        $this->signal1
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $this->signal2
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertFalse($this->instance->isEmpty());
    }
}
