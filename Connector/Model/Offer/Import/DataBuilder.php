<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import;

use Mirakl\Connector\Model\Offer\Import\Customizer\CustomizerInterface;
use Mirakl\Connector\Model\Offer\Import\Formatter\FormatterInterface;

class DataBuilder implements DataBuilderInterface
{
    /**
     * @var CustomizerInterface[]
     */
    private array $customizers;

    /**
     * @var FormatterInterface[]
     */
    private array $formatters;

    /**
     * @param CustomizerInterface[] $customizers
     * @param FormatterInterface[]  $formatters
     */
    public function __construct(
        array $customizers = [],
        array $formatters = []
    ) {
        $this->customizers = $customizers;
        $this->formatters = $formatters;
    }

    /**
     * @inheritdoc
     */
    public function build(array $offer): array
    {
        foreach ($this->customizers as $customizer) {
            $customizer->customize($offer);
        }

        foreach ($this->formatters as $formatter) {
            $formatter->format($offer);
        }

        return $offer;
    }
}
