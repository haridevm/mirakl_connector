<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Product;

use Mirakl\Api\Helper\Config as ApiConfig;
use Mirakl\Core\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Helper\Config as ConfigHelper;
use Mirakl\Mcm\Helper\Product\Export\Process as ProcessHelper;
use Mirakl\Mcm\Helper\Product\Export\Product as ProductHelper;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

abstract class AbstractObserver
{
    /**
     * @var ApiConfig
     */
    protected $apiConfigHelper;

    /**
     * @var McmHelper
     */
    protected $mcmHelper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ProcessHelper
     */
    protected $processHelper;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param ApiConfig                $apiConfigHelper
     * @param McmHelper                $mcmHelper
     * @param ConfigHelper             $configHelper
     * @param ProcessHelper            $processHelper
     * @param ProductHelper            $productHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProcessFactory           $processFactory
     * @param ProcessResourceFactory   $processResourceFactory
     */
    public function __construct(
        ApiConfig $apiConfigHelper,
        McmHelper $mcmHelper,
        ConfigHelper $configHelper,
        ProcessHelper $processHelper,
        ProductHelper $productHelper,
        ProductCollectionFactory $productCollectionFactory,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory
    ) {
        $this->apiConfigHelper          = $apiConfigHelper;
        $this->mcmHelper                = $mcmHelper;
        $this->configHelper             = $configHelper;
        $this->processHelper            = $processHelper;
        $this->productHelper            = $productHelper;
        $this->enabled                  = $this->configHelper->isMcmEnabled() || $this->configHelper->isAsyncMcmEnabled(); // phpcs:ignore
        $this->productCollectionFactory = $productCollectionFactory;
        $this->processFactory           = $processFactory;
        $this->processResourceFactory   = $processResourceFactory;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->apiConfigHelper->isEnabled() && $this->enabled;
    }
}
