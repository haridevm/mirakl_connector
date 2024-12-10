<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mirakl\Core\Model\ResourceModel\ArraySerializableFieldsTrait;
use Mirakl\Process\Model\File\StorageInterface;
use Mirakl\Process\Model\Process as ProcessModel;

/**
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Process extends AbstractDb
{
    use ArraySerializableFieldsTrait;

    /**
     * @var array
     */
    protected $_serializableFields = [
        'params' => [null, []]
    ];

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @param Context          $context
     * @param StorageInterface $storage
     * @param string|null      $connectionName
     */
    public function __construct(
        Context $context,
        StorageInterface $storage,
        string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mirakl_process', 'id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Mirakl\Process\Model\Process $object */
        if (!$object->getHash()) {
            $object->generateHash();
        }

        $currentTime = date('Y-m-d H:i:s');
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);

        parent::_beforeSave($object);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        if ($file = $object->getData('file')) {
            $this->storage->removeFile($file);
        }

        if ($miraklFile = $object->getData('mirakl_file')) {
            $this->storage->removeFile($miraklFile);
        }

        return parent::_beforeDelete($object);
    }

    /**
     * Deletes specified processes from database
     *
     * @param array $ids
     * @return bool|int
     */
    public function deleteIds(array $ids)
    {
        if (!empty($ids)) {
            return $this->getConnection()->delete($this->getMainTable(), ['id IN (?)' => $ids]);
        }

        return false;
    }

    /**
     * Mark expired processes execution as TIMEOUT according to specified delay in minutes
     *
     * @param int $delay
     * @return int
     * @throws \Exception
     */
    public function markAsTimeout($delay)
    {
        $delay = abs(intval($delay));
        if (!$delay) {
            throw new \InvalidArgumentException('Delay for expired processes cannot be empty');
        }

        $now = date('Y-m-d H:i:s');
        $timestampDiffExpr = new \Zend_Db_Expr(sprintf(
            "TIMESTAMPDIFF(MINUTE, updated_at, '%s') > %d",
            $now,
            $delay
        ));

        $result = $this->getConnection()->update(
            $this->getMainTable(),
            [
                'status' => ProcessModel::STATUS_TIMEOUT,
                'updated_at' => $now,
            ],
            [
                'status = ?' => ProcessModel::STATUS_PROCESSING,
                strval($timestampDiffExpr) => ProcessModel::STATUS_TIMEOUT
            ]
        );

        return $result;
    }

    /**
     * Overrides this in order to not unset object that calls __destruct() otherwise
     *
     * @param AbstractModel $object
     * @return array
     */
    protected function prepareDataForUpdate($object)
    {
        return $this->_prepareDataForTable($object, $this->getMainTable());
    }

    /**
     * Truncate mirakl_process table
     */
    public function truncate()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
