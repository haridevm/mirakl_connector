<?php
namespace Mirakl\Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirakl\Connector\Model\Shop\Import\Handler as ShopImportHandler;
use Mirakl\Core\Model\Shop as ShopModel;
use Mirakl\Core\Model\ResourceModel\ShopFactory as ShopResourceFactory;
use Mirakl\Core\Model\ShopFactory;
use Mirakl\Process\Model\Process;

class Shop extends AbstractHelper
{
    const CODE = 'S20';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ShopFactory
     */
    protected $shopFactory;

    /**
     * @var ShopResourceFactory
     */
    protected $shopResourceFactory;

    /**
     * @var ShopImportHandler
     */
    private $shopImportHandler;

    /**
     * @param Context $context
     * @param Config $config
     * @param ShopFactory $shopFactory
     * @param ShopResourceFactory $shopResourceFactory
     * @param ShopImportHandler $shopImportHandler
     */
    public function __construct(
        Context $context,
        Config $config,
        ShopFactory $shopFactory,
        ShopResourceFactory $shopResourceFactory,
        ShopImportHandler $shopImportHandler
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->shopFactory = $shopFactory;
        $this->shopResourceFactory = $shopResourceFactory;
        $this->shopImportHandler = $shopImportHandler;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @deprecated Use Mirakl\Connector\Model\Shop\Import\Handler::execute() instead
     *
     * @param Process $process
     * @param \DateTime|null $since
     * @return array
     * @throws \Exception
     */
    public function synchronize(Process $process, $since = null)
    {
        $params['since'] = $since;
        $params['full'] = !$since;

        return $this->shopImportHandler->execute($process, ...$params);
    }
    /**
     * Retrieve shop based on given shop id
     *
     * @param int $shopId
     * @return ShopModel
     */
    public function getShopById($shopId)
    {
        $shop = $this->shopFactory->create();
        $this->shopResourceFactory->create()->load($shop, $shopId);

        return $shop;
    }
}