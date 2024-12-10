<?php

declare(strict_types=1);

namespace Mirakl\Core\Test\Integration;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class TestCase extends \Mirakl\Core\Test\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ProcessFactory;
     */
    protected $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->processFactory = $this->objectManager->create(ProcessFactory::class);
        $this->processResourceFactory = $this->objectManager->create(ProcessResourceFactory::class);
    }
}
