<?php

namespace Tourze\LockCommandBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class LockCommandExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
