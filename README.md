# Symfony Lock Command Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)

A Symfony bundle that provides locking mechanism for console commands to prevent concurrent execution.

## Features

- Automatic command locking based on command class and input parameters
- Prevents multiple instances of the same command from running simultaneously
- Easy integration with Symfony console commands
- Configurable lock duration (default: 60 minutes)
- Automatic lock release after command completion or termination
- Intelligent lock key generation using command class name and input parameters
- Graceful command disabling when lock cannot be acquired
- Comprehensive logging for lock acquisition and release events

## Installation

You can install the package via composer:

```bash
composer require tourze/symfony-lock-command-bundle
```

## Quick Start

1. Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Tourze\LockCommandBundle\LockCommandBundle::class => ['all' => true],
];
```

2. Create a lockable command by extending `LockableCommand`:

```php
<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

class YourCommand extends LockableCommand
{
    protected static $defaultName = 'app:your-command';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Your command logic here
        $output->writeln('Command is running...');
        
        // Simulate some work
        sleep(5);
        
        $output->writeln('Command completed successfully!');
        return Command::SUCCESS;
    }
}
```

The command will automatically be locked based on the command class name and input parameters.

## How It Works

- When a command extends `LockableCommand`, the bundle automatically generates a unique lock key based on the command class name and input parameters
- Lock keys are generated using MD5 hash of JSON-encoded arguments and options
- Lock duration is set to 60 minutes by default
- If a lock cannot be acquired, the command is disabled and a warning is logged
- Locks are automatically released when the command terminates

## Configuration

The bundle uses Symfony's Lock component with the default lock factory. You can configure the lock store in your Symfony configuration:

```yaml
# config/packages/lock.yaml
framework:
    lock:
        # Configure your preferred lock store
        # Examples: flock, semaphore, redis, etc.
        default: 'flock'
```

## Advanced Usage

### Custom Lock Key Generation

You can override the `getLockKey()` method to customize lock key generation:

```php
<?php

use Symfony\Component\Console\Input\InputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

class CustomLockCommand extends LockableCommand
{
    protected static $defaultName = 'app:custom-lock';

    public function getLockKey(InputInterface $input): ?string
    {
        // Custom lock key logic
        $userId = $input->getArgument('user-id');
        return 'custom_command_' . $userId;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Command logic
        return Command::SUCCESS;
    }
}
```

### Returning Null to Disable Locking

If you want to conditionally disable locking for certain scenarios, return `null` from `getLockKey()`:

```php
public function getLockKey(InputInterface $input): ?string
{
    // Disable locking for admin users
    if ($input->getOption('admin')) {
        return null;
    }
    
    return parent::getLockKey($input);
}
```

## Testing

Run the tests with:

```bash
./vendor/bin/phpunit packages/symfony-lock-command-bundle/tests
```

### Test Commands

The bundle includes the following test commands for development and testing purposes:

- `test:lockable` - A test command that demonstrates the locking functionality. This command is used in the test suite to verify that the lock mechanism works correctly.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
