<?php
namespace ShoppingFeed\Signal;

class SignalHandlerFallbackTest extends HandlerTestCase
{
    /**
     * @var SignalHandlerFallback
     */
    private $instance;

    public function setUp(): void
    {
        $this->instance = new SignalHandlerFallback();
    }

    public function testIsAlwaysEmpty(): void
    {
        $this->assertTrue($this->instance->isEmpty());

        $this->instance->append('is_string');
        $this->assertTrue($this->instance->isEmpty());

        $this->instance->prepend('is_string');
        $this->assertTrue($this->instance->isEmpty());
    }

    public function testClearContract(): void
    {
        $this->assertSame($this->instance, $this->instance->clear());
    }

    public function testHandleDoNothing(): void
    {
        $cb = $this->createMock(HandlerTestStub::class);
        $cb->expects($this->never())->method('__invoke');

        $this->instance->append($cb);
        $this->instance->handle(0);
    }
}
