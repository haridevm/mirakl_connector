<?php

declare(strict_types=1);

namespace Mirakl\Sync\Model\Sync\Script;

use Mirakl\Sync\Model\Sync\Script;

/**
 * @method Script getItemById($id)
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_itemObjectClass = Script::class;
}
