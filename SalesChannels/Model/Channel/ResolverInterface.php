<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Channel;

interface ResolverInterface
{
    /**
     * This method returns the current Mirakl sales channel
     * according to the current store and other business logic.
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function resolve(int $storeId = null): ?string;
}
