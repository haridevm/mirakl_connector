<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer\Channel;

use Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector\FieldCollectorInterface;
use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

class DataOverrider implements DataOverriderInterface
{
    /**
     * @var FieldCollectorInterface[]
     */
    private array $fieldCollectors;

    /**
     * @param array $fieldCollectors
     */
    public function __construct(array $fieldCollectors = [])
    {
        $this->fieldCollectors = $fieldCollectors;
    }

    /**
     * {@inheritdoc}
     */
    public function override(ChannelOfferInterface $offer): void
    {
        foreach ($this->fieldCollectors as $fieldCollector) {
            $data = $fieldCollector->collect($offer);
            $offer->addData($data);
        }
    }
}
