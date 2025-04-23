<?php

namespace Tourze\LockCommandBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Tourze\LockCommandBundle\EventSubscriber\LockCommandEventSubscriber;

class LockCommandEventSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = LockCommandEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(ConsoleEvents::COMMAND, $events);
        $this->assertArrayHasKey(ConsoleEvents::TERMINATE, $events);

        // 检查命令事件有适当的优先级
        $this->assertArrayHasKey(0, $events[ConsoleEvents::COMMAND]);
        $this->assertEquals('onConsoleCommand', $events[ConsoleEvents::COMMAND][0][0]);
        $this->assertEquals(100, $events[ConsoleEvents::COMMAND][0][1]);

        // 检查终止事件有适当的优先级
        $this->assertArrayHasKey(0, $events[ConsoleEvents::TERMINATE]);
        $this->assertEquals('onConsoleTerminate', $events[ConsoleEvents::TERMINATE][0][0]);
        $this->assertEquals(-1000, $events[ConsoleEvents::TERMINATE][0][1]);
    }
}
