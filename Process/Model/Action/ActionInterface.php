<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

use Mirakl\Process\Model\Process;

interface ActionInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param Process $process
     * @param array   $params
     * @return array
     */
    public function execute(Process $process, ...$params): array;

    /**
     * @param array $params
     * @return void
     */
    public function addParams(array $params): void;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void;
}
