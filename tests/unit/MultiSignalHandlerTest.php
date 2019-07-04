<?php
namespace ShoppingFeed\Signal;

use PHPUnit\Framework\TestCase;

class MultiSignalHandlerTest extends TestCase
{
    /**
     * @var MultiSignalHandler
     */
    private $instance;

    /**
     * @var SignalHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $signal1;

    /**
     * @var SignalHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $signal2;

    public function setUp()
    {
        $this->signal1  = $this->createMock(SignalHandlerInterface::class);
        $this->signal2  = $this->createMock(SignalHandlerInterface::class);
        $this->instance = new MultiSignalHandler([1 => $this->signal1, 2 => $this->signal2]);
    }

    public function testHandleSignalForwardToSignalThatHandleSigno()
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

    public function testHandleSignalForwardToNoSignal()
    {
        $this->signal1
            ->expects($this->never())
            ->method('handle');

        $this->signal2
            ->expects($this->never())
            ->method('handle');

        $this->instance->handle(3, 'test');
    }

    public function testAppendAddNewCallabackToAllSignals()
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

    public function testPrependAddNewCallabackToAllSignals()
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

    public function testClearIsInvokedOnAllSignals()
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

    public function testIsEmptyExpectsAllSignalAreEmpty()
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

    public function testIsNotEmptyWhenOneSignalIsEmpty()
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

    public function testIsNotEmptyWhenAllSignalsAreNotEmpty()
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
