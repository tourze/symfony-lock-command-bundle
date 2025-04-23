<?php

namespace Tourze\LockCommandBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\LockCommandBundle\LockCommandBundle;

class LockCommandBundleTest extends TestCase
{
    public function testBundleInstance(): void
    {
        $bundle = new LockCommandBundle();

        $this->assertInstanceOf(LockCommandBundle::class, $bundle);
    }
}
