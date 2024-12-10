<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

class Boolean implements FormatterInterface
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
     * @inheritdoc
     */
    public function format(array &$offer): void
    {
        foreach ($this->fields as $key => $field) {
            if (isset($offer[$key])) {
                $offer[$field] = $offer[$key] ? 'true' : 'false';
                if ($key !== $field) {
                    unset($offer[$key]);
                }
            }
        }
    }
}
