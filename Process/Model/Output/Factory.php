<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Output;

use Magento\Framework\ObjectManagerInterface;
use Mirakl\Process\Model\Process;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string  $type
     * @param Process $process
     * @return OutputInterface
     */
    public function create(string $type, Process $process): OutputInterface
    {
        $type = ucfirst($type);
        $instanceName = __NAMESPACE__ . "\\$type";
        if (!class_exists($instanceName)) {
            $instanceName = NullOutput::class;
        }

        return $this->objectManager->create($instanceName, ['process' => $process]);
    }
}
