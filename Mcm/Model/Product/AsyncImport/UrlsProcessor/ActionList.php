<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport\UrlsProcessor;

use Mirakl\Mcm\Model\Product\AsyncImport\GetProductsExportFileFactory;
use Mirakl\Process\Model\Action\ActionListInterface;

class ActionList implements ActionListInterface
{
    /**
     * @var GetProductsExportFileFactory
     */
    private $getProductsExportFileFactory;

    /**
     * @param GetProductsExportFileFactory $getProductsExportFileFactory
     */
    public function __construct(GetProductsExportFileFactory $getProductsExportFileFactory)
    {
        $this->getProductsExportFileFactory = $getProductsExportFileFactory;
    }

    /**
     * @return bool
     */
    public function areParamsChainable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function get(array $params = []): \Generator
    {
        $urls = $params['urls'] ?? [];
        foreach ($urls as $key => $url) {
            $getProductsExportFile = $this->getProductsExportFileFactory->create();
            $getProductsExportFile->setKey($key + 1);
            $getProductsExportFile->setMaxRetry(1);
            $getProductsExportFile->addParams(['url' => $url]);
            yield $getProductsExportFile;
        }
    }
}