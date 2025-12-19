<?php

declare(strict_types=1);

namespace Tourze\LockCommandBundle\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\LockCommandBundle\Interface\LockHistoryLoggerInterface;

#[AutoconfigureTag(name: 'as-coroutine')]
#[WithMonologChannel(channel: 'lock_command')]
final class LockCommandEventSubscriber implements EventSubscriberInterface, ResetInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [
                ['onConsoleCommand', 100],
            ],
            ConsoleEvents::TERMINATE => [
                // 尽可能晚点释放这个锁，让逻辑都处理完成了
                ['onConsoleTerminate', -1000],
            ],
        ];
    }

    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly LoggerInterface $logger,
        private readonly ?LockHistoryLoggerInterface $lockHistoryLogger = null,
    ) {
    }

    private ?LockInterface $lock = null;

    private ?string $currentCommandClass = null;

    private ?string $currentLockKey = null;

    private ?\DateTimeImmutable $lockAcquiredAt = null;

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command instanceof LockableCommand) {
            return;
        }

        $key = $command->getLockKey($event->getInput());
        if (null === $key || '' === $key) {
            return;
        }

        $input = $event->getInput();
        $commandClass = get_class($command);
        $lock = $this->lockFactory->createLock($key, 60 * 60);

        if ($lock->acquire()) {
            $this->handleLockAcquisition($lock, $commandClass, $key, $input);
        } else {
            $this->handleLockFailure($event, $commandClass, $key, $input);
        }
    }

    public function onConsoleTerminate(): void
    {
        if (null === $this->lock) {
            return;
        }

        try {
            $this->lock->release();
            $this->handleSuccessfulRelease();
        } catch (\Throwable $exception) {
            $this->handleReleaseException($exception);
        }

        $this->resetState();
    }

    public function reset(): void
    {
        $this->resetState();
    }

    private function handleLockAcquisition(
        LockInterface $lock,
        string $commandClass,
        string $key,
        InputInterface $input,
    ): void {
        $this->lock = $lock;
        $this->currentCommandClass = $commandClass;
        $this->currentLockKey = $key;
        $this->lockAcquiredAt = new \DateTimeImmutable();

        $this->recordLockAcquired($commandClass, $key, $input->getArguments(), $input->getOptions());
    }

    private function handleLockFailure(
        ConsoleCommandEvent $event,
        string $commandClass,
        string $key,
        InputInterface $input,
    ): void {
        $event->disableCommand();
        $event->stopPropagation();

        $this->recordLockFailed(
            $commandClass,
            $key,
            '无法获取锁，可能有其他实例正在运行',
            $input->getArguments(),
            $input->getOptions()
        );

        $this->logger->warning('因为拿不到锁而停止执行命令:' . $commandClass, [
            'command' => $event->getCommand(),
            'event' => $event,
        ]);
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     */
    private function recordLockAcquired(string $commandClass, string $key, array $arguments, array $options): void
    {
        if (null === $this->lockHistoryLogger) {
            return;
        }

        try {
            $this->lockHistoryLogger->logLockAcquired($commandClass, $key, $arguments, $options);
        } catch (\Throwable $e) {
            $this->logger->error('记录锁定历史失败', ['exception' => $e]);
        }
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     */
    private function recordLockFailed(
        string $commandClass,
        string $key,
        string $reason,
        array $arguments = [],
        array $options = [],
    ): void {
        if (null === $this->lockHistoryLogger) {
            return;
        }

        try {
            $this->lockHistoryLogger->logLockFailed($commandClass, $key, $reason, $arguments, $options);
        } catch (\Throwable $e) {
            $this->logger->error('记录锁定失败历史失败', ['exception' => $e]);
        }
    }

    private function recordLockReleased(string $commandClass, string $key, int $durationSeconds): void
    {
        if (null === $this->lockHistoryLogger) {
            return;
        }

        try {
            $this->lockHistoryLogger->logLockReleased($commandClass, $key, $durationSeconds);
        } catch (\Throwable $e) {
            $this->logger->error('记录锁定释放失败', ['exception' => $e]);
        }
    }

    private function handleSuccessfulRelease(): void
    {
        if (null === $this->currentCommandClass || null === $this->currentLockKey) {
            return;
        }

        $durationSeconds = 0;
        if (null !== $this->lockAcquiredAt) {
            $durationSeconds = (new \DateTimeImmutable())->getTimestamp() - $this->lockAcquiredAt->getTimestamp();
        }

        $this->recordLockReleased($this->currentCommandClass, $this->currentLockKey, $durationSeconds);
    }

    private function handleReleaseException(\Throwable $exception): void
    {
        $this->logger->error('释放Command锁时发生错误', ['exception' => $exception]);

        if (null !== $this->currentCommandClass && null !== $this->currentLockKey) {
            $this->recordLockFailed(
                $this->currentCommandClass,
                $this->currentLockKey,
                '释放锁时发生错误: ' . $exception->getMessage()
            );
        }
    }

    private function resetState(): void
    {
        $this->lock = null;
        $this->currentCommandClass = null;
        $this->currentLockKey = null;
        $this->lockAcquiredAt = null;
    }
}
