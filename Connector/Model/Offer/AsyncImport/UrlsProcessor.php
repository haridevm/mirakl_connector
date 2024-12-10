<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\AsyncImport;

use Mirakl\Process\Model\Action\AbstractParentAction;

class UrlsProcessor extends AbstractParentAction
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'API OF53 URLs Processor';
    }
}