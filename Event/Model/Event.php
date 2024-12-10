<?php

declare(strict_types=1);

namespace Mirakl\Event\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;
use Mirakl\Catalog\Helper\Config as CatalogConfigHelper;
use Mirakl\Mci\Helper\Config as MciConfigHelper;
use Mirakl\Mcm\Helper\Config as McmConfigHelper;

/**
 * @method int    getAction()
 * @method $this  setAction(int $action)
 * @method string getCode()
 * @method $this  setCode(string $code)
 * @method string getCreatedAt()
 * @method $this  setCreatedAt(string $createdAt)
 * @method $this  setCsvData(string $csvData)
 * @method string getImportId()
 * @method $this  setImportId(int $importId)
 * @method string getLine()
 * @method $this  setLine(int $line)
 * @method string getMessage()
 * @method $this  setMessage(string $message)
 * @method string getProcessId()
 * @method $this  setProcessId(int $processId)
 * @method string getStatus()
 * @method $this  setStatus(string $status)
 * @method int    getType()
 * @method $this  setType(int $type)
 * @method string getUpdatedAt()
 * @method $this  setUpdatedAt(string $updatedAt)
 *
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Event extends AbstractModel
{
    public const STATUS_WAITING        = 'waiting';
    public const STATUS_PROCESSING     = 'processing';
    public const STATUS_SENT           = 'sent';
    public const STATUS_SUCCESS        = 'success';
    public const STATUS_INTERNAL_ERROR = 'internal_error';
    public const STATUS_MIRAKL_ERROR   = 'mirakl_error';

    public const TYPE_VL01 = 1;
    public const TYPE_H01  = 2;
    public const TYPE_PM01 = 3;
    public const TYPE_CA01 = 4;
    public const TYPE_P21  = 5;
    public const TYPE_CM21 = 6;

    public const ACTION_PREPARE = 0;
    public const ACTION_UPDATE  = 1;
    public const ACTION_DELETE  = 2;

    /**
     * @var string
     */
    protected $_eventPrefix = 'mirakl_event';

    /**
     * @var string
     */
    protected $_eventObject = 'mirakl_event';

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @var array
     */
    protected static $_types = [
        self::TYPE_VL01 => 'Value Lists Synchronization (VL01)',
        self::TYPE_H01  => 'Catalog Categories Synchronization (H01)',
        self::TYPE_PM01 => 'Attributes Synchronization (PM01)',
        self::TYPE_CA01 => 'Marketplace Categories Synchronization (CA01)',
        self::TYPE_P21  => 'Products Synchronization (P21)',
        self::TYPE_CM21 => 'MCM Products Synchronization (CM21)',
    ];

    /**
     * @var array
     */
    protected static $_shortTypes = [
        self::TYPE_VL01 => 'VL01',
        self::TYPE_H01  => 'H01',
        self::TYPE_PM01 => 'PM01',
        self::TYPE_CA01 => 'CA01',
        self::TYPE_P21  => 'P21',
        self::TYPE_CM21 => 'CM21',
    ];

    /**
     * @var array
     */
    protected static $_syncConfigPath = [
        self::TYPE_VL01 => MciConfigHelper::XML_PATH_ENABLE_SYNC_VALUES_LISTS,
        self::TYPE_H01  => MciConfigHelper::XML_PATH_ENABLE_SYNC_HIERARCHIES,
        self::TYPE_PM01 => MciConfigHelper::XML_PATH_ENABLE_SYNC_ATTRIBUTES,
        self::TYPE_CA01 => CatalogConfigHelper::XML_PATH_ENABLE_SYNC_CATEGORIES,
        self::TYPE_P21  => CatalogConfigHelper::XML_PATH_ENABLE_SYNC_PRODUCTS,
        self::TYPE_CM21 => McmConfigHelper::XML_PATH_ENABLE_SYNC_MCM_PRODUCTS,
    ];

    /**
     * @var array
     */
    protected static $_actions = [
        self::ACTION_PREPARE => 'prepare',
        self::ACTION_UPDATE  => 'update',
        self::ACTION_DELETE  => 'delete',
    ];

    /**
     * @param Context               $context
     * @param Registry              $registry
     * @param Serialize             $serializer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Event::class);
    }

    /**
     * @return array
     */
    public static function getActions()
    {
        return self::$_actions;
    }

    /**
     * @return array
     */
    public function getCsvData()
    {
        $data = $this->_getData('csv_data');
        if (is_string($data)) {
            $data = $this->serializer->unserialize($data);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @return array|string
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
     * @return string
     */
    public function getStatusClass()
    {
        switch ($this->getStatus()) {
            case self::STATUS_WAITING:
                $class = 'grid-severity-minor';
                break;
            case self::STATUS_PROCESSING:
            case self::STATUS_SENT:
                $class = 'grid-severity-major';
                break;
            case self::STATUS_INTERNAL_ERROR:
            case self::STATUS_MIRAKL_ERROR:
                $class = 'grid-severity-critical';
                break;
            case self::STATUS_SUCCESS:
            default:
                $class = 'grid-severity-notice';
        }

        return $class;
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getSyncConfigPath($type)
    {
        return self::$_syncConfigPath[$type];
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getTypeLabel($type)
    {
        return self::$_types[$type];
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getShortTypeLabel($type)
    {
        return self::$_shortTypes[$type];
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$_types;
    }

    /**
     * @return array
     */
    public static function getShortTypes()
    {
        return self::$_shortTypes;
    }
}
