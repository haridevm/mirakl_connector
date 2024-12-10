<?php
declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Ui;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Mirakl\Process\Test\Integration\TestCase;
use Mirakl\Process\Ui\Component\DataProvider;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @group process
 */
class DataProviderTest extends TestCase
{
    /**
     * @var UiComponentFactory|null
     */
    private $componentFactory;

    /**
     * @var RequestInterface|null
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->componentFactory = $this->objectManager->get(UiComponentFactory::class);
    }

    /**
     * @magentoDataFixture Mirakl_Process::Test/Integration/_files/processes.php
     *
     * @return void
     */
    public function testParentProcessListing(): void
    {
        /**
         * Retrieve data for the index listing page
         */
        $dataProvider = $this->getComponentProvider('mirakl_process_listing');

        // Ensure that only process without parent are present
        $this->assertInstanceOf(DataProvider::class, $dataProvider);
        $data = $dataProvider->getData();
        $items = $data['items'];
        foreach ($items as $item) {
            $this->assertEmpty($item['parent_id']);
        }

        // Ensure that the url to update the bookmark (filter/pagination) doesn't contain process id
        $configData = $dataProvider->getConfigData();
        $this->assertStringNotContainsString('/parent_id/', $configData['update_url']);
    }

    /**
     * @magentoDataFixture Mirakl_Process::Test/Integration/_files/processes.php
     *
     * @return void
     */
    public function testChildProcessListing(): void
    {
        /**
         * Retrieve data for the listing in a process page
         */
        $collection = $this->findProcess('name', 'Process Completed');
        $parentId = $collection->getFirstItem()->getId();
        $this->request->setParams(['id' => $parentId]);

        $dataProvider = $this->getComponentProvider('mirakl_process_listing');

        // Ensure that only the process of the parent is present
        $data = $dataProvider->getData();
        $items = $data['items'];
        $this->assertCount(1, $items);
        $item = reset($items);
        $this->assertEquals('Child Process Completed', $item['name']);
        $this->assertEquals($parentId, $item['parent_id']);

        // Ensure that the url to update the bookmark (filter/pagination) contains the parent process id
        $configData = $dataProvider->getConfigData();
        $this->assertStringContainsString('/parent_id/' . $parentId, $configData['update_url']);
    }

    /**
     * Call prepare method in the child components
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareChildComponents(UiComponentInterface $component): void
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareChildComponents($child);
        }

        $component->prepare();
    }

    /**
     * @param  string $namespace
     * @return DataProviderInterface
     */
    private function getComponentProvider(string $namespace): DataProviderInterface
    {
        $component = $this->componentFactory->create($namespace);
        $this->prepareChildComponents($component);

        return $component->getContext()->getDataProvider();
    }
}
