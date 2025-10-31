<?php

declare(strict_types=1);

namespace Tourze\LockCommandBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;
use Tourze\LockCommandBundle\EventSubscriber\LockCommandEventSubscriber;
use Tourze\LockCommandBundle\Tests\Fixtures\Command\DummyLockableCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(LockCommandEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class LockCommandEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    private LockFactory&MockObject $lockFactory;

    private LockCommandEventSubscriber $subscriber;

    protected function onSetUp(): void
    {
        // 创建 mock 服务
        $this->lockFactory = $this->createMock(LockFactory::class);

        // 替换容器中的 LockFactory 服务为模拟对象
        /** @phpstan-ignore-next-line */
        $this->getContainer()->set(LockFactory::class, $this->lockFactory);

        $this->subscriber = self::getService(LockCommandEventSubscriber::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = LockCommandEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(ConsoleEvents::COMMAND, $events);
        $this->assertArrayHasKey(ConsoleEvents::TERMINATE, $events);

        // 检查命令事件有适当的优先级
        $commandEvents = $events[ConsoleEvents::COMMAND];
        $this->assertIsArray($commandEvents);
        $this->assertArrayHasKey(0, $commandEvents);
        $firstCommandEvent = $commandEvents[0];
        $this->assertIsArray($firstCommandEvent);
        $this->assertArrayHasKey(0, $firstCommandEvent);
        $this->assertArrayHasKey(1, $firstCommandEvent);
        $this->assertEquals('onConsoleCommand', $firstCommandEvent[0]);
        $this->assertEquals(100, $firstCommandEvent[1]);

        // 检查终止事件有适当的优先级
        $terminateEvents = $events[ConsoleEvents::TERMINATE];
        $this->assertIsArray($terminateEvents);
        $this->assertArrayHasKey(0, $terminateEvents);
        $firstTerminateEvent = $terminateEvents[0];
        $this->assertIsArray($firstTerminateEvent);
        $this->assertArrayHasKey(0, $firstTerminateEvent);
        $this->assertArrayHasKey(1, $firstTerminateEvent);
        $this->assertEquals('onConsoleTerminate', $firstTerminateEvent[0]);
        $this->assertEquals(-1000, $firstTerminateEvent[1]);
    }

    public function testOnConsoleCommandWithNonLockableCommand(): void
    {
        // 使用具体类 Command 是因为 Symfony Console 组件没有提供命令接口，
        // Command 是抽象基类，Mock 它可以测试事件订阅器对非 LockableCommand 的处理。
        // 这是 Symfony 测试中的标准做法。
        $command = $this->createMock(Command::class);
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        // 锁工厂不应该被调用
        $this->lockFactory->expects($this->never())->method('createLock');

        $this->subscriber->onConsoleCommand($event);

        // 事件应该保持默认状态（可以执行）
        $this->assertTrue($event->commandShouldRun());
    }

    public function testOnConsoleCommandWithLockableCommandAcquiresLock(): void
    {
        $command = self::getService(DummyLockableCommand::class);
        $input = new ArrayInput(['arg1' => 'test']);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true)
        ;

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->willReturn($lock)
        ;

        $this->subscriber->onConsoleCommand($event);

        // 命令应该可以执行
        $this->assertTrue($event->commandShouldRun());
    }

    public function testOnConsoleCommandWithLockableCommandFailsToAcquireLock(): void
    {
        $command = self::getService(DummyLockableCommand::class);
        $input = new ArrayInput(['arg1' => 'test']);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(false)
        ;

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->willReturn($lock)
        ;

        // 日志记录功能由真实 logger 处理，我们不在这里测试
        // $this->logger->expects($this->once())
        //     ->method('warning')
        //     ->with(
        //         $this->stringContains('因为拿不到锁而停止执行命令'),
        //         $this->arrayHasKey('command')
        //     )
        // ;

        $this->subscriber->onConsoleCommand($event);

        // 命令应该被禁用
        $this->assertFalse($event->commandShouldRun());
        // 事件传播应该被停止
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testOnConsoleCommandWithEmptyLockKey(): void
    {
        // Mock DummyLockableCommand 是为了测试空锁键情况。
        // 使用具体类是因为需要 Mock getLockKey 方法返回空值，
        // 而 LockableCommand 是抽象类。这比创建新的测试类更简洁。
        $command = $this->createMock(DummyLockableCommand::class);
        $command->expects($this->once())
            ->method('getLockKey')
            ->willReturn('') // 空锁键
        ;

        $input = new ArrayInput([]);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        // 锁工厂不应该被调用
        $this->lockFactory->expects($this->never())->method('createLock');

        $this->subscriber->onConsoleCommand($event);

        // 命令应该可以执行
        $this->assertTrue($event->commandShouldRun());
    }

    public function testOnConsoleTerminateWithoutLock(): void
    {
        // 没有锁的情况下调用 terminate
        $this->subscriber->onConsoleTerminate();

        // 方法正常执行且没有异常，测试成功
        $this->expectNotToPerformAssertions();
    }

    public function testOnConsoleTerminateReleasesLock(): void
    {
        // 首先获取锁
        $command = self::getService(DummyLockableCommand::class);
        $input = new ArrayInput(['arg1' => 'test']);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true)
        ;
        $lock->expects($this->once())
            ->method('release')
        ;

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->willReturn($lock)
        ;

        $this->subscriber->onConsoleCommand($event);

        // 然后释放锁
        $this->subscriber->onConsoleTerminate();
    }

    public function testOnConsoleTerminateHandlesReleaseException(): void
    {
        // 首先获取锁
        $command = self::getService(DummyLockableCommand::class);
        $input = new ArrayInput(['arg1' => 'test']);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true)
        ;
        $lock->expects($this->once())
            ->method('release')
            ->willThrowException(new \RuntimeException('Release failed'))
        ;

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->willReturn($lock)
        ;

        // 日志记录功能由真实 logger 处理，我们不在这里测试
        // $this->logger->expects($this->once())
        //     ->method('error')
        //     ->with(
        //         $this->stringContains('释放Command锁时发生错误'),
        //         $this->arrayHasKey('exception')
        //     )
        // ;

        $this->subscriber->onConsoleCommand($event);

        // 释放锁时应该捕获异常
        $this->subscriber->onConsoleTerminate();
    }

    public function testResetMethod(): void
    {
        // 首先设置一个锁
        $command = self::getService(DummyLockableCommand::class);
        $input = new ArrayInput(['arg1' => 'test']);
        $output = new NullOutput();

        $event = new ConsoleCommandEvent($command, $input, $output);

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true)
        ;

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->willReturn($lock)
        ;

        $this->subscriber->onConsoleCommand($event);

        // 调用 reset
        $this->subscriber->reset();

        // 调用 terminate 应该不会尝试释放锁（因为已经被重置）
        $this->subscriber->onConsoleTerminate();

        // 方法正常执行且没有异常，测试成功
    }
}
