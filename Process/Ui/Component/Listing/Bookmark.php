<?php
declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirakl\Process\Model\Repository;

class Bookmark extends AbstractComponent
{
    const NAME = 'bookmark';

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param ContextInterface            $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param Repository                  $repository
     * @param array                       $components
     * @param array                       $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkManagementInterface $bookmarkManagement,
        Repository $repository,
        array $components = [],
        array $data = []
    ) {
        $this->bookmarkManagement = $bookmarkManagement;
        $this->repository = $repository;

        parent::__construct($context, $components, $data);
    }

    /**
     * @return string
     */
    public function getComponentName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(): void
    {
        // Code duplicated from \Magento\Ui\Component\Bookmark to add the process id in the namespace
        // This permit to have different filter/pagination in the main listing and each view listing
        $namespace = $this->getContext()->getRequestParam('namespace');
        $config = [];
        $namespaceConfig = [];
        if (!$namespace) {
            $namespace = $this->getContext()->getNamespace();
            $processId = $this->context->getRequestParam('id');
            if ($namespace && $process = $this->repository->get($processId)) {
                $namespace .=  '_' . $process->getId();
                $namespaceConfig = ['storageConfig' => ['namespace' => $namespace]];
            }
        }

        if (!empty($namespace)) {
            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
            foreach ($bookmarks->getItems() as $bookmark) {
                if ($bookmark->isCurrent()) {
                    $config['activeIndex'] = $bookmark->getIdentifier();
                }

                $config = array_merge_recursive($config, $bookmark->getConfig());
            }
        }

        $this->setData('config', array_replace_recursive($config, $this->getConfiguration(), $namespaceConfig));

        parent::prepare();

        $jsConfig = $this->getConfiguration();
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}
