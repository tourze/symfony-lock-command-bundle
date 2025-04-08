<?php

namespace Tourze\LockCommandBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Yiisoft\Json\Json;

abstract class LockableCommand extends Command
{
    /**
     * 获得锁
     */
    public function getLockKey(InputInterface $input): ?string
    {
        $params = [
            $input->getArguments(),
            $input->getOptions(),
        ];
        $params = Json::encode($params);
        $params = md5($params);

        return str_replace('\\', '_', static::class) . '_' . $params;
    }
}
