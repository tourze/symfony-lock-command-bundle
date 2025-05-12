<?php

namespace Tourze\LockCommandBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

#[AutoconfigureTag('as-coroutine')]
class LockCommandEventSubscriber implements EventSubscriberInterface, ResetInterface
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
    ) {
    }

    private ?LockInterface $lock = null;

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command instanceof LockableCommand) {
            return;
        }

        $key = $command->getLockKey($event->getInput());
        if (!$key) {
            return;
        }
        $lock = $this->lockFactory->createLock($key, 60 * 60);
        if ($lock->acquire()) {
            // 暂存起来
            $this->lock = $lock;

            return;
        }

        // 拿不到锁，可能有其他人在执行，跳过
        $event->disableCommand();
        $event->stopPropagation();
        $this->logger->warning('因为拿不到锁而停止执行命令:' . get_class($command), [
            'command' => $command,
            'event' => $event,
        ]);
    }

    public function onConsoleTerminate(): void
    {
        if (!$this->lock) {
            return;
        }

        try {
            $this->lock->release();
        } catch (\Throwable $exception) {
            $this->logger->error('释放Command锁时发生错误', [
                'exception' => $exception,
            ]);
        }
        $this->lock = null;
    }

    public function reset(): void
    {
        $this->lock = null;
    }
}
