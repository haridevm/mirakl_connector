<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\AsyncImport\UrlsProcessor;

use Mirakl\Connector\Model\Offer\AsyncImport\GetOffersExportFileFactory;
use Mirakl\Process\Model\Action\ActionListInterface;

class ActionList implements ActionListInterface
{
    /**
     * @var GetOffersExportFileFactory
     */
    private $getOffersExportFileFactory;

    /**
     * @param GetOffersExportFileFactory $getOffersExportFileFactory
     */
    public function __construct(GetOffersExportFileFactory $getOffersExportFileFactory)
    {
        $this->getOffersExportFileFactory = $getOffersExportFileFactory;
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
            $getOffersExportFile = $this->getOffersExportFileFactory->create();
            $getOffersExportFile->setKey($key + 1);
            $getOffersExportFile->setMaxRetry(1);
            $getOffersExportFile->addParams(['url' => $url]);
            yield $getOffersExportFile;
        }
    }
}
