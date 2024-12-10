<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport;

use Mirakl\Process\Model\Action\AbstractParentAction;

class UrlsProcessor extends AbstractParentAction
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'API CM53 URLs Processor';
    }
}
