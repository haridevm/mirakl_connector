<?php
namespace Mirakl\Core\Test\Integration;

use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

class TestCase extends \Mirakl\Core\Test\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
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

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->processFactory = $this->objectManager->create(ProcessFactory::class);
        $this->processResourceFactory = $this->objectManager->create(ProcessResourceFactory::class);
    }
}
