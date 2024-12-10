<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport;

use Mirakl\Process\Model\Action\AbstractParentAction;

class Import extends AbstractParentAction
{
    const CODE = 'CM52';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'CM52-CM53-CM54 synchronization';
    }
}