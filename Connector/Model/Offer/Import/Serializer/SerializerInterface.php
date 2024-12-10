<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Serializer;

interface SerializerInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string;
}