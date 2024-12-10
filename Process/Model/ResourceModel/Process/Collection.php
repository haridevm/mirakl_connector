<?php
namespace Mirakl\Process\Model\ResourceModel\Process;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\Process;

/**
 * @method Process getFirstItem()
 */
class Collection extends AbstractCollection
{
    /**
     * Set resource model
     */
    protected function _construct()
    {
        $this->_init(Process::class, \Mirakl\Process\Model\ResourceModel\Process::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        /** @var Process $item */
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }

        return parent::_afterLoad();
    }

    /**
     * @param   ActionInterface $action
     * @return  Collection
     */
    public function addActionFilter(ActionInterface $action)
    {
        $this->addFieldToFilter('name', $action->getName())
            ->addFieldToFilter('helper', get_class($action));

        return $this;
    }

    /**
     * Adds API Type filter to current collection
     *
     * @return  $this
     */
    public function addApiTypeFilter()
    {
        return $this->addFieldToFilter('type', 'API');
    }

    /**
     * Adds completed status filter to current collection
     *
     * @return  $this
     */
    public function addCompletedFilter()
    {
        return $this->addStatusFilter(Process::STATUS_COMPLETED);
    }

    /**
     * Excludes processes that have the same hash as the given ones
     *
     * @param   string|array    $hash
     * @return  $this
     */
    public function addExcludeHashFilter($hash)
    {
        if (empty($hash)) {
            return $this;
        }

        if (!is_array($hash)) {
            $hash = [$hash];
        }

        return $this->addFieldToFilter('hash', ['nin' => $hash]);
    }

    /**
     * Adds idle status filter to current collection
     *
     * @return  $this
     */
    public function addIdleFilter()
    {
        return $this->addStatusFilter(Process::STATUS_IDLE);
    }

    /**
     * @param   int $parentId
     * @return  $this
     */
    public function addParentFilter($parentId)
    {
        $this->addFieldToFilter('parent_id', $parentId);

        return $this;
    }

    /**
     * Adds pending statuses filter to current collection
     *
     * @return  $this
     */
    public function addPendingFilter()
    {
        return $this->addFieldToFilter('status', ['in' => [
            Process::STATUS_PENDING,
            Process::STATUS_PENDING_RETRY,
        ]]);
    }

    /**
     * Adds processing status filter to current collection
     *
     * @return  $this
     */
    public function addProcessingFilter()
    {
        return $this->addStatusFilter(Process::STATUS_PROCESSING);
    }

    /**
     * Adds processing status filter to current collection for mirakl_status field
     *
     * @return  $this
     */
    public function addMiraklProcessingFilter()
    {
        return $this->addFieldToFilter('mirakl_status', Process::STATUS_PROCESSING);
    }

    /**
     * Adds pending status filter to current collection for mirakl_status field
     *
     * @return  $this
     */
    public function addMiraklPendingFilter()
    {
        return $this->addFieldToFilter('mirakl_status', Process::STATUS_PENDING);
    }

    /**
     * @param   string|array    $status
     * @return  $this
     */
    public function addStatusFilter($status)
    {
        return $this->addFieldToFilter('status', $status);
    }

    /**
     * @return  $this
     */
    public function cancel()
    {
        $this->setDataToAll('status', Process::STATUS_CANCELLED);
        $this->save();

        return $this;
    }
}
