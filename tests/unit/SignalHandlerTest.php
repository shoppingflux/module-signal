<?php
namespace ShoppingFeed\Signal;

class SignalHandlerTest extends HandlerTestCase
{
    /**
     * @var SignalHandler
     */
    private $instance;

    protected function setUp()
    {
        $this->instance = new SignalHandler(SIGUSR1);
    }

    public function testHanldeSignalWithMultiplesCallback(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->once())
            ->method('__invoke')
            ->with(SIGUSR1);

        $cb2 = $this->createMock(HandlerTestStub::class);
        $cb2->expects($this->once())
            ->method('__invoke')
            ->with(SIGUSR1);

        $this->instance->append($cb1)->append($cb2);
        $this->send(SIGUSR1);
    }

    public function testDoesNotHandleAnySignalWhenCallbackIsCleared(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->never())
            ->method('__invoke');

        $this->instance->append($cb1);
        $this->instance->clear();

        $this->send(SIGUSR1);
    }

    public function testDoesNotHandleAnySignalWhenCallbackIsClearedForSpecificGroup(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->never())
            ->method('__invoke');

        $cb2 = $this->createMock(HandlerTestStub::class);
        $cb2->expects($this->once())
            ->method('__invoke')
            ->with(SIGUSR1);

        $this->instance
            ->append($cb1, ['group' => 'test'])
            ->append($cb2)
            ->clear('test');

        $this->send(SIGUSR1);
    }

    public function testDoesNotHandleAnySignalWhenCallbackIsClearedForAllGroups(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->never())
            ->method('__invoke');

        $cb2 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->never())
            ->method('__invoke');

        $this->instance
            ->append($cb1, ['group' => 'test1'])
            ->append($cb2, ['group' => 'test2'])
            ->clear(['test1', 'test2']);

        $this->send(SIGUSR1);
    }

    public function testDoesNotHandleSignaTwice(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->once())
            ->method('__invoke');

        $this->instance->append($cb1, ['once' => true]);
        $this->send(SIGUSR1);
        $this->send(SIGUSR1);
    }

    public function testPreviousSignalCanBeRestored(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->once())
            ->method('__invoke');

        $cb2 = $this->createMock(HandlerTestStub::class);
        $cb2->expects($this->once())
            ->method('__invoke');

        // Register the first signal which is supposed to be called on second "send"
        $instanceA = new SignalHandler(SIGUSR1);
        $instanceA->append($cb1);

        // Register the second handler with restore mode and "once" callback
        $instanceB = new SignalHandler(SIGUSR1, SignalHandler::PREV_RESTORE);
        $instanceB->append($cb2, ['once' => true]);

        // Send the signal twice
        $this->send(SIGUSR1);
        $this->send(SIGUSR1);
    }

    public function testCannotOverridePreviousRegisteredSignal(): void
    {
        $cb = $this->createMock(HandlerTestStub::class);

        $instanceA = new SignalHandler(SIGUSR1);
        $instanceA->append($cb);

        $instanceB = new SignalHandler(SIGUSR1, SignalHandler::PREV_ERROR);

        $this->expectException(Exception\RuntimeException::class);
        $instanceB->append($cb);
    }

    public function testCheckIfTheCallbackStackIsEmpty(): void
    {
        $this->assertTrue($this->instance->isEmpty(), 'empty by default');

        $this->instance->prepend($this->createMock(HandlerTestStub::class));
        $this->assertFalse($this->instance->isEmpty());
    }

    public function testFactoryReturnAnInstanceOfSignalHandlerWhenExtensionIsLoaded(): void
    {
        $instance = SignalHandler::factory(SIGUSR1);
        $this->assertInstanceOf(SignalHandler::class, $instance);
    }

    public function tearDown()
    {
        $this->instance->clear();
    }
}
