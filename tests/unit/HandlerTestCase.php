<?php
namespace ShoppingFeed\Signal;

use PHPUnit\Framework\TestCase;

abstract class HandlerTestCase extends TestCase
{
    protected function send($signal): void
    {
        posix_kill(posix_getpid(), $signal);
    }
}

class HandlerTestStub
{
    public function __invoke()
    {
    }
}
