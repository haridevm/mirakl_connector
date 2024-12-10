<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection;
use Mirakl\SalesChannels\Model\Offer\Channel\DataOverriderInterface;
use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

class OfferCollectionLoadAfterObserver implements ObserverInterface
{
    /**
     * @var DataOverriderInterface
     */
    private $dataOverrider;

    /**
     * @param DataOverriderInterface $dataOverrider
     */
    public function __construct(DataOverriderInterface $dataOverrider)
    {
        $this->dataOverrider = $dataOverrider;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var Collection $collection */
        $collection = $observer->getEvent()->getOfferCollection();

        foreach ($collection as $offer) {
            if ($offer instanceof ChannelOfferInterface) {
                $this->dataOverrider->override($offer);
            }
        }
    }
}
