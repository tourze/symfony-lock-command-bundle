<?php

namespace Tourze\LockCommandBundle\Tests\Command\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\LockCommandBundle\Tests\Fixtures\Command\DummyLockableCommand;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(DummyLockableCommand::class)]
#[RunTestsInSeparateProcesses]
final class DummyLockableCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(DummyLockableCommand::class);
        $this->assertInstanceOf(DummyLockableCommand::class, $command);
        $this->commandTester = new CommandTester($command);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    public function testCommandExists(): void
    {
        $command = self::getContainer()->get(DummyLockableCommand::class);

        $this->assertInstanceOf(DummyLockableCommand::class, $command);
        $this->assertSame(DummyLockableCommand::NAME, $command->getName());
        $this->assertSame('test:lockable', $command->getName());
        $this->assertSame('Test lockable command', $command->getDescription());
    }

    public function testCreateArrayInput(): void
    {
        $command = self::getContainer()->get(DummyLockableCommand::class);
        $this->assertInstanceOf(DummyLockableCommand::class, $command);
        $input = $command->createArrayInput(['arg1' => 'test', '--option1' => 'value']);

        $this->assertSame('test', $input->getArgument('arg1'));
        $this->assertSame('value', $input->getOption('option1'));
    }

    public function testArgumentArg1(): void
    {
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute([
            'arg1' => 'test-argument-value',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Test command executed successfully', $commandTester->getDisplay());
    }

    public function testOptionOption1(): void
    {
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute([
            'arg1' => 'required-argument',
            '--option1' => 'test-option-value',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Test command executed successfully', $commandTester->getDisplay());
    }

    public function testCommandExecution(): void
    {
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute([
            'arg1' => 'test-value',
            '--option1' => 'test-option',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Test command executed successfully', $commandTester->getDisplay());
    }
}
