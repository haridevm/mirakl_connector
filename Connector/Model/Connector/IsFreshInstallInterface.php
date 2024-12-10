<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Connector;

interface IsFreshInstallInterface
{
    /**
     * @return bool
     */
    public function execute(): bool;
}
