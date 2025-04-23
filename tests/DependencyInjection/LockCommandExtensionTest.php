<?php

namespace Tourze\LockCommandBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\LockCommandBundle\DependencyInjection\LockCommandExtension;
use Tourze\LockCommandBundle\EventSubscriber\LockCommandEventSubscriber;

class LockCommandExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new LockCommandExtension();

        $extension->load([], $container);

        // 检查事件订阅器是否已注册
        $this->assertTrue($container->hasDefinition(LockCommandEventSubscriber::class));
    }
}
