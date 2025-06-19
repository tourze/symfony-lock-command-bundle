<?php

namespace Tourze\LockCommandBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

class LockableCommandTest extends TestCase
{
    public function testGetLockKeyFormat(): void
    {
        /** @phpstan-ignore-next-line */
        $command = new #[AsCommand(name: 'test:command', description: 'Test command')] class extends LockableCommand {
            public const NAME = 'test:command';
            
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        };

        $input = new ArrayInput([
            'arg1' => 'value1',
            '--option1' => 'option-value1',
        ]);

        $lockKey = $command->getLockKey($input);

        // 确保生成了锁的键
        $this->assertNotNull($lockKey);
        // 确保类名在键中
        $commandClass = str_replace('\\', '_', get_class($command));
        $this->assertStringContainsString($commandClass, $lockKey);

        // 验证锁键格式：类名_哈希值
        $this->assertMatchesRegularExpression('/^' . preg_quote($commandClass, '/') . '_[a-f0-9]{32}$/', $lockKey);
    }

    public function testGetLockKeyConsistency(): void
    {
        /** @phpstan-ignore-next-line */
        $command = new #[AsCommand(name: 'test:command', description: 'Test command')] class extends LockableCommand {
            public const NAME = 'test:command';
            
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        };

        // 用相同参数多次调用，应得到相同的键
        $input1 = new ArrayInput([
            'arg1' => 'value1',
            '--option1' => 'option-value1',
        ]);
        $lockKey1 = $command->getLockKey($input1);

        $input2 = new ArrayInput([
            'arg1' => 'value1',
            '--option1' => 'option-value1',
        ]);
        $lockKey2 = $command->getLockKey($input2);

        $this->assertSame($lockKey1, $lockKey2, '使用相同参数应生成相同的锁键');
    }
}
