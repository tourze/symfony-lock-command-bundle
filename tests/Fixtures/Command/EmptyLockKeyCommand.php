<?php

declare(strict_types=1);

namespace Tourze\LockCommandBundle\Tests\Fixtures\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

/**
 * 用于测试空锁键场景的命令
 */
#[AsCommand(name: 'test:empty-lock', description: 'Test command with empty lock key')]
final class EmptyLockKeyCommand extends LockableCommand
{
    public function getLockKey(InputInterface $input): ?string
    {
        return ''; // 空锁键
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
