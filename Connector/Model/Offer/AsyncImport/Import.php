<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\AsyncImport;

use Mirakl\Process\Model\Action\AbstractParentAction;

class Import extends AbstractParentAction
{
    const CODE = 'OF52';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'OF52-OF53-OF54 synchronization';
    }
}