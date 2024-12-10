<?php

declare(strict_types=1);

namespace Mirakl\Sync\Model\Sync;

use Magento\Framework\DataObject;
use Mirakl\Core\Helper\Config as MiraklConfig;

/**
 * @method string getCode()
 * @method $this  setCode(string $code)
 * @method array  getConfig()
 * @method $this  setConfig(array $config)
 * @method string getHelper()
 * @method $this  setHelper(string $helper)
 * @method bool   getMethod()
 * @method $this  setMethod(string $method)
 * @method string getTitle()
 * @method $this  setTitle(string $title)
 */
class Script extends DataObject
{
    /**
     * @var MiraklConfig
     */
    protected $miraklConfig;

    /**
     * @var Script\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Script\CollectionFactory $collectionFactory
     * @param MiraklConfig             $miraklConfig
     * @param array                    $data
     */
    public function __construct(
        Script\CollectionFactory $collectionFactory,
        MiraklConfig $miraklConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->miraklConfig = $miraklConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Script\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getCode();
    }

    /**
     * @return bool
     */
    public function isSyncDisable()
    {
        if (empty($this->getConfig())) {
            return false;
        }

        // Verify if script config values are valid or not.
        // If one config value is different in Magento config, script is disabled.
        foreach ($this->getConfig() as $path => $value) {
            if ($this->miraklConfig->getValue($path) != $value) {
                return true;
            }
        }

        return false;
    }
}
