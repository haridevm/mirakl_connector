<?php

declare(strict_types=1);

namespace Mirakl\Event\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Serialize;
use Mirakl\Event\Helper\Process\ExportType;
use Mirakl\Event\Model\EventFactory;
use Mirakl\Event\Model\Event;
use Mirakl\Event\Model\ResourceModel\EventFactory as EventResourceFactory;
use Mirakl\Event\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Process\Model\ResourceModel\Process\Collection as ProcessCollection;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    public const PROCESS_NAME = 'Export event queue';

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResourceFactory
     */
    protected $eventResourceFactory;

    /**
     * @var EventCollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @var ProcessCollectionFactory
     */
    protected $processCollectionFactory;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @param Context                  $context
     * @param EventFactory             $eventFactory
     * @param EventResourceFactory     $eventResourceFactory
     * @param EventCollectionFactory   $eventCollectionFactory
     * @param ProcessFactory           $processFactory
     * @param ProcessResourceFactory   $processResourceFactory
     * @param ProcessCollectionFactory $processCollectionFactory
     * @param Serialize                $serializer
     */
    public function __construct(
        Context $context,
        EventFactory $eventFactory,
        EventResourceFactory $eventResourceFactory,
        EventCollectionFactory $eventCollectionFactory,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        ProcessCollectionFactory $processCollectionFactory,
        Serialize $serializer
    ) {
        parent::__construct($context);
        $this->eventFactory = $eventFactory;
        $this->eventResourceFactory = $eventResourceFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->processCollectionFactory = $processCollectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param string            $code
     * @param int               $action
     * @param int               $type
     * @param string|array|null $csvData
     * @return Event
     */
    public function addEvent($code, $action, $type, $csvData = null)
    {
        $event = $this->eventFactory->create();

        /** @var \Mirakl\Event\Model\ResourceModel\Event\Collection $collection */
        $collection = $this->eventCollectionFactory->create();
        $collection->addCodeFilter($code)
            ->addTypeFilter($type)
            ->addWaitingFilter();

        if (count($collection)) {
            $event = $collection->getFirstItem();
        } else {
            $event->setCode($code);
            $event->setType($type);
            $event->setStatus(Event::STATUS_WAITING);
        }

        $event->setAction($action);
        $event->setCsvData(is_array($csvData) ? $this->serializer->serialize($csvData) : $csvData);
        $this->eventResourceFactory->create()->save($event);

        return $event;
    }

    /**
     * Returns current running event processes (should contains only one max)
     *
     * @return ProcessCollection
     */
    private function getRunningProcesses()
    {
        /** @var ProcessCollection $processing */
        $processing = $this->processCollectionFactory->create();
        $processing->addProcessingFilter()
            ->addFieldToFilter('name', self::PROCESS_NAME);

        return $processing;
    }

    /**
     * Get event process in idle status or create a new one
     *
     * @param string      $processType
     * @param string|null $eventType
     * @return Process
     * @throws AlreadyRunningException
     */
    public function getOrCreateEventProcess($processType, $eventType = null)
    {
        // Verify that no other event is running
        $runningProcesses = $this->getRunningProcesses();

        if (count($runningProcesses)) {
            throw new AlreadyRunningException(
                $runningProcesses->getFirstItem(),
                __('Another event process is running. Please try again later.')
            );
        }

        // No event is currently running, try to find one in idle status or create a new one

        /** @var ProcessCollection $collection */
        $collection = $this->processCollectionFactory->create();
        $collection->addIdleFilter()
            ->addFieldToFilter('name', self::PROCESS_NAME);

        /** @var Process $process */
        if (count($collection)) {
            $process = $collection->getFirstItem();
        } else {
            $eventTypeId = $eventType ? array_search($eventType, Event::getShortTypes()) : null;
            $executeAllTypes = !$eventTypeId;
            $process = $this->processFactory->create()
                ->setStatus(Process::STATUS_IDLE)
                ->setType($processType)
                ->setName(self::PROCESS_NAME)
                ->setHelper(ExportType::class)
                ->setMethod('execute')
                ->setParams([$eventTypeId ?: Event::TYPE_VL01, Event::ACTION_DELETE, $executeAllTypes]);
            $this->processResourceFactory->create()->save($process);
        }

        return $process;
    }
}
