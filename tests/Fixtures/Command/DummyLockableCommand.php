<?php

declare(strict_types=1);

namespace Tourze\LockCommandBundle\Tests\Fixtures\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

/**
 * 用于测试的具体 LockableCommand 实现
 */
#[AsCommand(name: self::NAME, description: 'Test lockable command')]
class DummyLockableCommand extends LockableCommand
{
    public const NAME = 'test:lockable';

    /**
     * @param array<string, mixed> $parameters
     */
    public function createArrayInput(array $parameters): InputInterface
    {
        return new ArrayInput($parameters, $this->getDefinition());
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1')
            ->addOption('option1')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Test command executed successfully');

        return Command::SUCCESS;
    }
}
