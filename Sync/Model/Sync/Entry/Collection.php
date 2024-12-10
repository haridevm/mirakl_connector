<?php

declare(strict_types=1);

namespace Mirakl\Sync\Model\Sync\Entry;

use Mirakl\Sync\Model\Sync\Entry;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_itemObjectClass = Entry::class;
}
