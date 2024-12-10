<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Serializer;

class Tuples implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize(array $data): string
    {
        return \Mirakl\tuples_to_query_param($data);
    }
}