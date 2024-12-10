<?php
declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

class ChildProcessFactory implements ChildProcessFactoryInterface
{
    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @var string
     */
    private $defaultStatus;

    /**
     * @param ProcessFactory         $processFactory
     * @param ProcessResourceFactory $processResourceFactory
     * @param string                 $defaultStatus
     */
    public function __construct(
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        string $defaultStatus = Process::STATUS_IDLE
    ) {
        $this->processFactory = $processFactory;
        $this->processResource = $processResourceFactory->create();
        $this->defaultStatus = $defaultStatus;
    }

    /**
     * @inheritdoc
     * @throws ChildProcessException
     */
    public function create(Process $parent, ActionInterface $childAction): Process
    {
        if (!$parent->getId()) {
            throw new ChildProcessException($parent, __('Cannot create a child process under an unknown parent.'));
        }

        $childProcess = $this->processFactory->create();
        $childProcess->setStatus($this->defaultStatus)
            ->setParentId($parent->getId())
            ->setType($parent->getType())
            ->setName($childAction->getName())
            ->setHelper(get_class($childAction))
            ->setMethod('execute')
            ->setParams($childAction->getParams());

        $this->processResource->save($childProcess);

        $childProcess->setAction($childAction);
        $childProcess->setParent($parent);

        return $childProcess;
    }
}