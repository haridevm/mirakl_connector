<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

interface ActionListInterface
{
    /**
     * @param array $params
     * @return \Generator
     */
    public function get(array $params = []): \Generator;

    /**
     * @return bool
     */
    public function areParamsChainable(): bool;
}
