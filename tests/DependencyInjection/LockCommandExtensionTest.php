<?php

namespace Tourze\LockCommandBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\LockCommandBundle\DependencyInjection\LockCommandExtension;
use Tourze\LockCommandBundle\EventSubscriber\LockCommandEventSubscriber;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(LockCommandExtension::class)]
final class LockCommandExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 此测试不需要特殊的设置
    }

    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $extension = new LockCommandExtension();

        $extension->load([], $container);

        // 检查事件订阅器是否已注册
        $this->assertTrue($container->hasDefinition(LockCommandEventSubscriber::class));
    }
}
