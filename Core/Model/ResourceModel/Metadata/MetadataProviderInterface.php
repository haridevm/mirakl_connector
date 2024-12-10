<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Metadata;

interface MetadataProviderInterface
{
    /**
     * @return array
     */
    public function getFields(): array;

    /**
     * @return array
     */
    public function getDefaults(): array;
}
