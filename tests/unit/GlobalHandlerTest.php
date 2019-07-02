<?php
namespace ShoppingFeed\Signal;

use PHPUnit\Framework\TestCase;

class GlobalHandlerTest extends TestCase
{
    /**
     * @var GlobalHandler
     */
    private $instance;

    /**
     * @var GroupHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $group;

    protected function setUp()
    {
        $this->group    = $this->createMock(GroupHandler::class);
        $this->instance = new GlobalHandler();
    }


    public function testManageGroupHandler()
    {
        $group = $this->instance->group('test');
        $this->assertInstanceOf(GroupHandler::class, $group);
        $this->assertSame('test', $group->getName());

        $this->assertSame($group, $this->instance->group('test'), 'groups are built only once');
    }

    public function testManageSignalHandler()
    {
        $signal = $this->instance->signal(SIGUSR1);
        $this->assertInstanceOf(SignalHandler::class, $signal);

        $visited = false;
        $signal->append(function() use (&$visited) {
            $visited = true;
        });
        $signal->handle(SIGUSR1);
        $this->assertTrue($visited, 'signal has been built with the provided signo');

        $this->assertSame($signal, $this->instance->signal(SIGUSR1), 'signals are built only once');
    }

    public function tearDown()
    {
        $this->instance->clear();
    }
}
