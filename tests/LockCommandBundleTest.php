<?php

declare(strict_types=1);

namespace Tourze\LockCommandBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\LockCommandBundle\LockCommandBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(LockCommandBundle::class)]
#[RunTestsInSeparateProcesses]
final class LockCommandBundleTest extends AbstractBundleTestCase
{
}
