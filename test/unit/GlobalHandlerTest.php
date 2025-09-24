<?php

namespace ShoppingFeed\Signal;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GlobalHandlerTest extends TestCase
{
    private GlobalHandler $instance;

    private GroupHandler|MockObject $group;

    protected function setUp(): void
    {
        $this->group    = $this->createMock(GroupHandler::class);
        $this->instance = new GlobalHandler();
    }

    public function testManageGroupHandler(): void
    {
        $group = $this->instance->group('test');
        $this->assertInstanceOf(GroupHandler::class, $group);
        $this->assertSame('test', $group->getName());

        $this->assertSame($group, $this->instance->group('test'), 'groups are built only once');
    }

    public function testManageSignalHandler(): void
    {
        $signal = $this->instance->signal(SIGUSR1);
        $this->assertInstanceOf(SignalHandler::class, $signal);

        $visited = false;
        $signal->append(function () use (&$visited) {
            $visited = true;
        });
        $signal->handle(SIGUSR1);
        $this->assertTrue($visited, 'signal has been built with the provided signo');

        $this->assertSame($signal, $this->instance->signal(SIGUSR1), 'signals are built only once');
    }

    public function testBuildMultiSignalHandler(): void
    {
        $handler = $this->instance->signals([SIGINT, SIGUSR1]);
        $this->assertInstanceOf(MultiSignalHandler::class, $handler);
    }

    public function tearDown(): void
    {
        $this->instance->clear();
    }
}
