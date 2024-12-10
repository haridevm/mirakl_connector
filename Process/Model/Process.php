<?php
namespace Mirakl\Process\Model;

use Magento\Framework\Data\Collection\AbstractDb as AbstractDbCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Mirakl\Process\Helper\Config as ProcessConfig;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\Output\Factory as OutputFactory;
use Mirakl\Process\Model\Output\OutputInterface;
use Mirakl\Process\Model\ProcessFactory as ProcessModelFactory;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Process\Model\ResourceModel\Process\Collection as ProcessCollection;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;

/**
 * @method string      getCode()
 * @method $this       setCode(string $code)
 * @method string|int  getCreatedAt()
 * @method $this       setCreatedAt(string|int $createdAt)
 * @method $this       setDuration(int|null $duration)
 * @method string      getErrorReport()
 * @method $this       setErrorReport(string $report)
 * @method string      getFile()
 * @method $this       setFile(string $file)
 * @method string      getHash()
 * @method $this       setHash(string $hash)
 * @method $this       setHelper(string $helper)
 * @method $this       setMethod(string $method)
 * @method string      getMiraklFile()
 * @method $this       setMiraklFile(string $file)
 * @method string      getMiraklStatus()
 * @method $this       setMiraklStatus(string $status)
 * @method string      getSuccessReport()
 * @method $this       setSuccessReport(string $report)
 * @method string      getSynchroId()
 * @method $this       setSynchroId(string $synchroId)
 * @method int         getTimeout()
 * @method $this       setTimeout(int $timeout)
 * @method string      getMiraklType()
 * @method $this       setMiraklType(string $type)
 * @method string      getName()
 * @method $this       setName(string $name)
 * @method string|null getOutput()
 * @method $this       setOutput(string|null $output)
 * @method int         getParentId()
 * @method $this       setParentId(int $parentId)
 * @method $this       setParams(string|array $params)
 * @method bool        getQuiet()
 * @method $this       setQuiet(bool $flag)
 * @method string      getStatus()
 * @method $this       setStatus(string $status)
 * @method string      getType()
 * @method $this       setType(string $type)
 * @method string      getUpdatedAt()
 * @method $this       setUpdatedAt(string $updatedAt)
 */
class Process extends AbstractModel
{
    const STATUS_PENDING       = 'pending';
    const STATUS_PENDING_RETRY = 'pending_retry';
    const STATUS_PROCESSING    = 'processing';
    const STATUS_IDLE          = 'idle';
    const STATUS_COMPLETED     = 'completed';
    const STATUS_STOPPED       = 'stopped';
    const STATUS_TIMEOUT       = 'timeout';
    const STATUS_ERROR         = 'error';
    const STATUS_CANCELLED     = 'cancelled';

    const TYPE_API        = 'API';
    const TYPE_CLI        = 'CLI';
    const TYPE_ADMIN      = 'ADMIN';
    const TYPE_IMPORT     = 'IMPORT';
    const TYPE_IMPORT_MCM = 'IMPORT_MCM';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mirakl_process';

    /**
     * @var string
     */
    protected $_eventObject = 'process';

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var OutputInterface[]
     */
    protected $outputs = [];

    /**
     * @var float
     */
    protected $startedAt;

    /**
     * @var bool
     */
    protected $stopped = false;

    /**
     * @var ActionInterface|null
     */
    protected $action;

    /**
     * @var Process|null
     */
    protected $parent;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var ProcessConfig
     */
    private $processConfig;

    /**
     * @var ProcessModelFactory
     */
    private $processModelFactory;

    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @var ProcessCollectionFactory
     */
    private $processCollectionFactory;

    /**
     * @var OutputFactory
     */
    private $outputFactory;

    /**
     * @var Execution
     */
    private $execution;

    /**
     * @var CheckMiraklStatus
     */
    private $checkMiraklStatus;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param Context                   $context
     * @param Registry                  $registry
     * @param UrlInterface              $urlBuilder
     * @param ProcessHelper             $processHelper
     * @param ProcessConfig             $processConfig
     * @param ProcessModelFactory       $processModelFactory
     * @param ProcessResourceFactory    $processResourceFactory
     * @param ProcessCollectionFactory  $processCollectionFactory
     * @param OutputFactory             $outputFactory
     * @param Execution                 $execution
     * @param CheckMiraklStatus         $checkMiraklStatus
     * @param ObjectManagerInterface    $objectManager
     * @param AbstractResource|null     $resource
     * @param AbstractDbCollection|null $resourceCollection
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $urlBuilder,
        ProcessHelper $processHelper,
        ProcessConfig $processConfig,
        ProcessModelFactory $processModelFactory,
        ProcessResourceFactory $processResourceFactory,
        ProcessCollectionFactory $processCollectionFactory,
        OutputFactory $outputFactory,
        Execution $execution,
        CheckMiraklStatus $checkMiraklStatus,
        ObjectManagerInterface $objectManager,
        AbstractResource $resource = null,
        AbstractDbCollection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->urlBuilder               = $urlBuilder;
        $this->processHelper            = $processHelper;
        $this->processConfig            = $processConfig;
        $this->processModelFactory      = $processModelFactory;
        $this->processResource          = $processResourceFactory->create();
        $this->processCollectionFactory = $processCollectionFactory;
        $this->outputFactory            = $outputFactory;
        $this->execution                = $execution;
        $this->checkMiraklStatus        = $checkMiraklStatus;
        $this->objectManager            = $objectManager;
    }

    /**
     * Initialize model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Process::class);
    }

    /**
     * Stops current process execution on shutdown
     */
    public function __destruct()
    {
        if ($this->isStarted() && !$this->isEnded()) {
            if ($output = ob_get_clean()) {
                $this->output($output);
            }
            $this->stop();
        }
    }

    /**
     * @param string|OutputInterface $output
     * @return $this
     * @throws \Exception
     */
    public function addOutput($output)
    {
        if (is_string($output)) {
            $output = $this->outputFactory->create($output, $this);
        }

        if (!$output instanceof OutputInterface) {
            throw new \Exception('Invalid output specified.');
        }

        $this->outputs[$output->getType()] = $output;

        return $this;
    }

    /**
     * Marks current process as cancelled and stops execution
     *
     * @param string|null $message
     * @return $this
     */
    public function cancel($message = null)
    {
        $this->execution->cancel($this, $message);

        return $this;
    }

    /**
     * Returns true if we can check Mirakl API status on process
     *
     * @return bool
     */
    public function canCheckMiraklStatus()
    {
        return !$this->isProcessing() &&
            ($this->getMiraklStatus() == self::STATUS_PENDING || $this->getMiraklStatus() == self::STATUS_PROCESSING);
    }

    /**
     * Returns true if process can be executed or not
     *
     * @return bool
     */
    public function canRun()
    {
        $parent = $this->getParent();

        return !$this->isProcessing()
            && !$this->isStatusIdle()
            && (!$parent || $parent->isEnded())
            && $this->getHelper()
            && $this->getMethod();
    }

    /**
     * @param bool $isMirakl
     * @return bool
     */
    public function canShowFile($isMirakl = false)
    {
        $fileSize = $this->getFileSize($isMirakl);

        return $fileSize <= ($this->processConfig->getShowFileMaxSize() * 1024 * 1024); // less than 5 MB
    }

    /**
     * Returns true if process can be set to STOPPED status
     *
     * @return bool
     */
    public function canStop()
    {
        return $this->isProcessing();
    }

    /**
     * @deprecated Use CheckMiraklStatus class instead
     * @return $this
     */
    public function checkMiraklStatus()
    {
        $this->checkMiraklStatus->execute($this);

        return $this;
    }

    /**
     * @return ActionInterface|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param ActionInterface $action
     * @return Process
     */
    public function setAction(ActionInterface $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelper()
    {
        return (string) $this->_getData('helper');
    }

    /**
     * @return mixed
     */
    public function getHelperInstance()
    {
        return $this->objectManager->create($this->getHelper());
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return (string) $this->_getData('method');
    }

    /**
     * @return float|null
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @param float $startedAt
     * @return $this
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = (float) $startedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return null !== $this->getStartedAt();
    }

    /**
     * @return bool
     */
    public function getStopped()
    {
        return $this->stopped;
    }

    /**
     * @param bool $stopped
     * @return $this
     */
    public function setStopped($stopped = true)
    {
        $this->stopped = (bool) $stopped;

        return $this;
    }

    /**
     * Calls current process helper->method()
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->execution->execute($this);
    }

    /**
     * Marks current process as failed and stops execution
     *
     * @param string|null $message
     * @return $this
     */
    public function fail($message = null)
    {
        $this->execution->fail($this, $message);

        return $this;
    }

    /**
     * @return ProcessCollection
     */
    public function getChildrenCollection()
    {
        return $this->processCollectionFactory->create()->addParentFilter($this->getId());
    }

    /**
     * @return $this
     */
    public function generateHash()
    {
        $hash = $this->processHelper->generateHash($this->getType(), $this->getName());
        $this->setHash($hash);

        return $this;
    }

    /**
     * @return int|\DateInterval
     */
    public function getDuration()
    {
        $duration = $this->_getData('duration');
        if (!$duration) {
            if ($this->isProcessing() || $this->isStatusIdle()) {
                $start = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getCreatedAt());
                $duration = $start->diff(new \DateTime());
            } elseif ($this->isEnded()){
                $start = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getCreatedAt());
                $end = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getUpdatedAt());
                $duration = $start->diff($end);
            }
        }

        return $duration;
    }

    /**
     * Returns file size in bytes
     *
     * @param bool $isMirakl
     * @return int
     */
    public function getFileSize($isMirakl = false)
    {
        $filePath = $isMirakl ? $this->getMiraklFile() : $this->getFile();

        return $this->processHelper->getFileSize($filePath);
    }

    /**
     * Returns process file download URL for admin
     *
     * @param bool $isMirakl
     * @return string|false
     */
    public function getDownloadFileUrl($isMirakl = false)
    {
        $file = $isMirakl ? $this->getMiraklFile() : $this->getFile();

        if (!$file) {
            return false;
        }

        return $this->urlBuilder->getUrl('mirakl/process/downloadFile', [
            'id' => $this->getId(),
            'mirakl' => $isMirakl,
        ]);
    }

    /**
     * Returns file size formatted
     *
     * @param string $separator
     * @param bool   $isMirakl
     * @return string|false
     */
    public function getFileSizeFormatted($separator = ' ', $isMirakl = false)
    {
        if ($fileSize = $this->getFileSize($isMirakl)) {
            return $this->processHelper->formatSize($fileSize, $separator);
        }

        return false;
    }

    /**
     * Returns output size in bytes
     *
     * @return bool|int
     */
    public function getOutputSize()
    {
        if (!empty($this->getOutput())) {
            return mb_strlen($this->getOutput(), 'utf-8');
        }

        return false;
    }

    /**
     * Returns process output download URL for admin
     *
     * @return string|false
     */
    public function getDownloadOutputUrl()
    {
        if (empty($this->getOutput())) {
            return false;
        }

        return $this->urlBuilder->getUrl('mirakl/process/downloadOutput', [
            'id' => $this->getId(),
        ]);
    }

    /**
     * Returns output size formatted
     *
     * @param string $separator
     * @return string|false
     */
    public function getOutputSizeFormatted($separator = ' ')
    {
        if ($size = $this->getOutputSize()) {
            return $this->processHelper->formatSize($size, $separator);
        }

        return false;
    }

    /**
     * @param bool $isMirakl
     * @return string|false
     */
    public function getFileUrl($isMirakl = false)
    {
        $file = $isMirakl ? $this->getMiraklFile() : $this->getFile();

        if (!$file || $this->processHelper->getFileSize($file) == 0) {
            return false;
        }

        return $this->processHelper->getFileUrl($file);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $params = $this->_getData('params');
        if (is_string($params)) {
            $params = unserialize($params);
        }

        return is_array($params) ? $params : [];
    }

    /**
     * @return Process|null
     */
    public function getParent()
    {
        if (!$this->parent && $this->getParentId()) {
            $this->parent = $this->processModelFactory->create();
            $this->processResource->load($this->parent, $this->getParentId());
        }

        return $this->parent;
    }

    /**
     * @param Process $parent
     * @return Process
     */
    public function setParent(Process $parent)
    {
        $this->parent = $parent;
        $this->setParentId($parent->getId());

        return $this;
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        static $statuses;

        if (!$statuses) {
            $class = new \ReflectionClass(__CLASS__);
            foreach ($class->getConstants() as $name => $value) {
                if (0 === strpos($name, 'STATUS_')) {
                    $statuses[$value] = $value;
                }
            }
        }

        return $statuses;
    }

    /**
     * @param bool $isMirakl
     * @return string
     */
    public function getStatusClass($isMirakl = false)
    {
        return self::getClassForStatus($isMirakl ? $this->getMiraklStatus() : $this->getStatus());
    }

    /**
     * @param string $status
     * @return string
     */
    public static function getClassForStatus(string $status): string
    {
        switch ($status) {
            case self::STATUS_PENDING:
            case self::STATUS_PENDING_RETRY:
            case self::STATUS_IDLE:
                $class = 'grid-severity-minor';
                break;
            case self::STATUS_PROCESSING:
                $class = 'grid-severity-major';
                break;
            case self::STATUS_STOPPED:
            case self::STATUS_CANCELLED:
            case self::STATUS_ERROR:
            case self::STATUS_TIMEOUT:
                $class = 'grid-severity-critical';
                break;
            case self::STATUS_COMPLETED:
            default:
                $class = 'grid-severity-notice';
        }

        return $class;
    }

    /**
     * Returns process URL for admin
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilder->getUrl('mirakl/process/view', [
            'id' => $this->getId()
        ]);
    }

    /**
     * @return $this
     */
    public function hr()
    {
        foreach ($this->outputs as $output) {
            $output->hr();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function markAsTimeout()
    {
        $this->updateDuration();
        $this->setStatus(self::STATUS_TIMEOUT);
        $this->processResource->save($this);

        $this->_eventManager->dispatch('mirakl_process_timeout', [
            'process' => $this,
        ]);

        return $this;
    }

    /**
     * Sets current process status to idle
     *
     * @return $this
     */
    public function idle()
    {
        return $this->setStatus(self::STATUS_IDLE);
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->getStatus() === self::STATUS_CANCELLED;
    }

    /**
     * @return  bool
     */
    public function isCompleted()
    {
        return $this->getStatus() === self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isEnded()
    {
        return $this->isCompleted()
            || $this->isStopped()
            || $this->isTimeout()
            || $this->isError()
            || $this->isCancelled();
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->getStatus() === self::STATUS_ERROR;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() === self::STATUS_PENDING || $this->getStatus() === self::STATUS_PENDING_RETRY;
    }

    /**
     * @return bool
     */
    public function isProcessing()
    {
        return $this->getStatus() === self::STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isStatusPendingRetry()
    {
        return $this->getStatus() === self::STATUS_PENDING_RETRY;
    }

    /**
     * @return bool
     */
    public function isStatusIdle()
    {
        return $this->getStatus() === self::STATUS_IDLE;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->getStatus() === self::STATUS_STOPPED;
    }

    /**
     * @return bool
     */
    public function isTimeout()
    {
        return $this->getStatus() === self::STATUS_TIMEOUT;
    }

    /**
     * @return OutputInterface[]
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    /**
     * @return bool
     */
    public function isParent()
    {
        return $this->getChildrenCollection()->count() !== 0;
    }

    /**
     * @return bool
     */
    public function isChild()
    {
        return $this->getParentId() !== null;
    }

    /**
     * @return bool
     */
    public function hasProcessingChild()
    {
        $children = $this->getChildrenCollection();
        foreach ($children as $child) {
            if ($child->isProcessing()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Outputs specified string in all associated output handlers
     *
     * @param string $str
     * @param bool   $save
     * @return $this
     */
    public function output($str, $save = false)
    {
        foreach ($this->outputs as $output) {
            $output->display($str);
        }

        return $this->appendOutput($str, $save);
    }

    /**
     * @param string $str
     * @param bool   $save
     * @return $this
     */
    public function appendOutput($str, $save = false)
    {
        $this->setData('output', trim($this->getOutput() . "\n" . $str));

        if ($save) {
            $this->processResource->save($this);
        }

        if ($parent = $this->getParent()) {
            $parent->appendOutput($str, $save);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function deleteChildren()
    {
        foreach ($this->getChildrenCollection() as $child) {
            /** @var Process $child */
            $child->deleteChildren();
            $this->processResource->delete($child);
        }
    }

    /**
     * Wraps process execution
     *
     * @param bool $force
     * @return mixed
     */
    public function run($force = false)
    {
        return $this->execution->run($this, $force);
    }

    /**
     * Starts current process
     *
     * @return $this
     */
    public function start()
    {
        $this->execution->start($this);

        return $this;
    }

    /**
     * Stops current process
     *
     * @param string $status
     * @return $this
     */
    public function stop($status = self::STATUS_COMPLETED)
    {
        $this->execution->stop($this, $status);

        return $this;
    }

    /**
     * Updates current process duration
     *
     * @return $this
     */
    public function updateDuration()
    {
        if (!$this->isProcessing()) {
            return $this;
        }

        if ($this->isStarted()) {
            $duration = intval(ceil(microtime(true) - $this->getStartedAt()));
        } elseif ($this->getCreatedAt()) {
            $start = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getCreatedAt());
            $duration = (new \DateTime())->getTimestamp() - $start->getTimestamp();
        }

        $duration = $this->getData('duration') + max(1, $duration ?? 0);
        $this->setDuration($duration);

        return $this;
    }

    /**
     * @return $this
     */
    public function outputMemoryUsage()
    {
        $this->hr();

        return $this->output(
            __('Memory Peak Usage: %1',
            $this->processHelper->formatSize(memory_get_peak_usage(true)))
        );
    }
}
