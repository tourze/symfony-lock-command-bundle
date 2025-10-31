# Symfony Lock Command Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-lock-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-lock-command-bundle)

一个为 Symfony 控制台命令提供锁机制的 Bundle，用于防止命令并发执行。

## 特性

- 基于命令类和输入参数的自动命令锁定
- 防止同一命令的多个实例同时运行
- 易于与 Symfony 控制台命令集成
- 可配置的锁定时长（默认：60 分钟）
- 命令完成或终止后自动释放锁
- 智能锁键生成，使用命令类名和输入参数
- 无法获取锁时优雅地禁用命令
- 全面的锁获取和释放事件日志记录

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
        // 你的命令逻辑
        $output->writeln('命令正在运行...');
        
        // 模拟一些工作
        sleep(5);
        
        $output->writeln('命令成功完成！');
        return Command::SUCCESS;
    }
}
```

命令会根据命令类名和输入参数自动进行锁定。

## 工作原理

- 当命令继承 `LockableCommand` 时，Bundle 会自动根据命令类名和输入参数生成唯一的锁键
- 锁键使用 JSON 编码的参数和选项的 MD5 哈希生成
- 锁定时长默认设置为 60 分钟
- 如果无法获取锁，命令会被禁用并记录警告日志
- 当命令终止时，锁会自动释放

## 配置

Bundle 使用 Symfony 的 Lock 组件和默认的锁工厂。你可以在 Symfony 配置中配置锁存储：

```yaml
# config/packages/lock.yaml
framework:
    lock:
        # 配置你偏好的锁存储
        # 示例：flock、semaphore、redis 等
        default: 'flock'
```

## 高级用法

### 自定义锁键生成

你可以重写 `getLockKey()` 方法来自定义锁键生成：

```php
<?php

use Symfony\Component\Console\Input\InputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;

class CustomLockCommand extends LockableCommand
{
    protected static $defaultName = 'app:custom-lock';

    public function getLockKey(InputInterface $input): ?string
    {
        // 自定义锁键逻辑
        $userId = $input->getArgument('user-id');
        return 'custom_command_' . $userId;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 命令逻辑
        return Command::SUCCESS;
    }
}
```

### 返回 Null 来禁用锁定

如果你想在某些情况下有条件地禁用锁定，可以从 `getLockKey()` 返回 `null`：

```php
public function getLockKey(InputInterface $input): ?string
{
    // 为管理员用户禁用锁定
    if ($input->getOption('admin')) {
        return null;
    }
    
    return parent::getLockKey($input);
}
```

## 测试

运行测试：

```bash
./vendor/bin/phpunit packages/symfony-lock-command-bundle/tests
```

### 测试命令

Bundle 包含以下用于开发和测试的测试命令：

- `test:lockable` - 演示锁定功能的测试命令。此命令在测试套件中用于验证锁机制是否正常工作。

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 开源协议

MIT 开源协议。详情请查看 [License 文件](LICENSE)。
