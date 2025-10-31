<?php

declare(strict_types=1);

namespace Tourze\LockCommandBundle\Interface;

/**
 * 可选的锁定历史记录接口
 *
 * 宿主应用可以实现此接口来记录命令锁定的历史信息。
 * 这是一个完全可选的功能，Bundle 不依赖于此接口的具体实现。
 */
interface LockHistoryLoggerInterface
{
    /**
     * 记录锁定成功获取
     *
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     */
    public function logLockAcquired(
        string $commandClass,
        string $lockKey,
        array $arguments = [],
        array $options = [],
    ): void;

    /**
     * 记录锁定正常释放
     */
    public function logLockReleased(
        string $commandClass,
        string $lockKey,
        int $durationSeconds,
    ): void;

    /**
     * 记录锁定获取失败或释放异常
     *
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     */
    public function logLockFailed(
        string $commandClass,
        string $lockKey,
        string $reason,
        array $arguments = [],
        array $options = [],
    ): void;
}
