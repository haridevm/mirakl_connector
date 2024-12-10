<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

use Mirakl\Core\Model\ResourceModel\Metadata\MetadataProviderInterface;

class TableColumns implements FormatterInterface
{
    /**
     * @var MetadataProviderInterface
     */
    private $metadataProvider;

    /**
     * @param MetadataProviderInterface $metadataProvider
     */
    public function __construct(MetadataProviderInterface $metadataProvider)
    {
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$offer): void
    {
        $cols = array_keys($this->metadataProvider->getFields());
        $defaults = $this->metadataProvider->getDefaults();

        $data = array_fill_keys($cols, '');
        $data = array_merge($data, array_intersect_key($offer, array_flip($cols)));
        $additionalCols = array_diff(array_keys($offer), $cols);

        if (!empty($additionalCols)) {
            $data['additional_info'] = array_intersect_key($offer, array_flip($additionalCols));
        }

        foreach ($data as $key => $value) {
            if (isset($defaults[$key]) && (null === $value || '' === $value)) {
                $data[$key] = $defaults[$key];
            }
        }

        $offer = $data;
    }
}
