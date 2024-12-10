<?php
namespace Mirakl\Mcm\Model\Product\Import\Adapter;

use Magento\Framework\ObjectManagerInterface;
use Mirakl\Mcm\Helper\Config;

class AdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * @return AdapterInterface
     */
    public function create()
    {
        $mode = $this->config->getProductsImportMode();

        switch ($mode) {
            case 'bulk':
                $class = Bulk::class;
                break;
            case 'standard':
            default:
                $class = Mcm::class;
        }

        return $this->objectManager->create($class);
    }
}