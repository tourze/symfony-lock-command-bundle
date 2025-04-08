# Symfony Lock Command Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)

一个为 Symfony 控制台命令提供锁机制的 Bundle，用于防止命令并发执行。

## 特性

- 基于命令类和输入参数的自动命令锁定
- 防止同一命令的多个实例同时运行
- 易于与 Symfony 控制台命令集成
- 可配置的锁定时长
- 命令完成后自动释放锁

## 安装

通过 composer 安装此包：

```bash
composer require tourze/symfony-lock-command-bundle
```

## 快速开始

1. 在 `config/bundles.php` 中注册此 bundle：

```php
return [
    // ...
    Tourze\LockCommandBundle\LockCommandBundle::class => ['all' => true],
];
```

2. 创建一个可锁定的命令，继承 `LockableCommand`：

```php
use Tourze\LockCommandBundle\Command\LockableCommand;

class YourCommand extends LockableCommand
{
    protected static $defaultName = 'app:your-command';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // 你的命令逻辑
        return Command::SUCCESS;
    }
}
```

命令会根据命令类名和输入参数自动进行锁定。

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 开源协议

MIT 开源协议。详情请查看 [License 文件](LICENSE)。
