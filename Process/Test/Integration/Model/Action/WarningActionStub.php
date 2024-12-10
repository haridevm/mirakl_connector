<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Process;

class WarningActionStub extends ActionStub
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute(Process $process, ...$params): array
    {
        $foo++; // @phpstan-ignore variable.undefined

        return [];
    }
}
