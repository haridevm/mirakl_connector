<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import;

class ChannelFields
{
    /**
     * @var array
     */
    private array $fields;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->fields;
    }
}