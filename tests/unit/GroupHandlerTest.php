<?php
namespace ShoppingFeed\Signal;

class GroupHandlerTest extends HandlerTestCase
{
    /**
     * @var GroupHandler
     */
    private $instance;

    protected function setUp()
    {
        $this->instance = new GroupHandler();
    }

    public function testRegisterAgainstSingleSignal()
    {
        $mock = $this->createMock(HandlerTestStub::class);
        $mock->expects($this->once())->method('__invoke');

        $this->instance->append(SIGUSR1, $mock);
        $this->send(SIGUSR1);
    }

    public function testMultiRegisterAgainstSingleSignal()
    {
        $mock1 = $this->createMock(HandlerTestStub::class);
        $mock1->expects($this->once())->method('__invoke');

        $mock2 = $this->createMock(HandlerTestStub::class);
        $mock2->expects($this->once())->method('__invoke');

        $this->instance->append(SIGUSR1, $mock1);
        $this->instance->append(SIGUSR1, $mock2);

        $this->send(SIGUSR1);
    }

    public function testMultiRegisterAgainstMultiplesSignals()
    {
        $mock1 = $this->createMock(HandlerTestStub::class);
        $mock1
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive([SIGUSR1], [SIGUSR2]);

        $mock2 = $this->createMock(HandlerTestStub::class);
        $mock2
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive([SIGUSR1], [SIGUSR2]);

        $this->instance->append([SIGUSR1, SIGUSR2], $mock1);
        $this->instance->append([SIGUSR1, SIGUSR2], $mock2);

        $this->send(SIGUSR1);
        $this->send(SIGUSR2);
    }

    public function testPrependCallbackAfterAppendedOne()
    {
        $tracker = [];
        $this->instance->append(SIGUSR1, function() use (&$tracker) {
            $tracker[] = 2;
        });
        $this->instance->prepend(SIGUSR1, function() use (&$tracker) {
            $tracker[] = 1;
        });

        $this->send(SIGUSR1);

        $this->assertSame(
            [1, 2],
            $tracker
        );
    }

    public function testCreateInstanceFromNamedConstructor(): void
    {
        $instance = GroupHandler::withSignals('test', [], SignalHandler::PREV_ERROR);
        $this->assertInstanceOf(GroupHandler::class, $instance);
        $this->assertSame('test', $instance->getName());
    }

    public function testDirectlyHandleMultiplesSignal(): void
    {
        $cb1 = $this->createMock(HandlerTestStub::class);
        $cb1->expects($this->once())
            ->method('__invoke')
            ->with(SIGUSR1);

        $cb2 = $this->createMock(HandlerTestStub::class);
        $cb2->expects($this->once())
            ->method('__invoke')
            ->with(SIGUSR2);

        $this->instance->append(SIGUSR1, $cb1);
        $this->instance->append(SIGUSR2, $cb2);

        $this->instance->handle([SIGUSR1, SIGUSR2]);
    }

    protected function tearDown()
    {
        $this->instance->clear();
    }
}
