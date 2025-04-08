# Symfony Lock Command Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)

A Symfony bundle that provides locking mechanism for console commands to prevent concurrent execution.

## Features

- Automatic command locking based on command class and input parameters
- Prevents multiple instances of the same command from running simultaneously
- Easy integration with Symfony console commands
- Configurable lock duration
- Automatic lock release after command completion

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
use Tourze\LockCommandBundle\Command\LockableCommand;

class YourCommand extends LockableCommand
{
    protected static $defaultName = 'app:your-command';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Your command logic here
        return Command::SUCCESS;
    }
}
```

The command will automatically be locked based on the command class name and input parameters.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
