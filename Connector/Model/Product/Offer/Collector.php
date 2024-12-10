<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\Product\Offer\Collector\Customizer\CustomizerInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;

class Collector implements CollectorInterface
{
    /**
     * @var OfferCollectionFactory
     */
    private $offerCollectionFactory;

    /**
     * @var CustomizerInterface[]
     */
    private $customizers;

    /**
     * @param OfferCollectionFactory $offerCollectionFactory
     * @param CustomizerInterface[] $customizers
     */
    public function __construct(
        OfferCollectionFactory $offerCollectionFactory,
        array $customizers = []
    ) {
        $this->offerCollectionFactory = $offerCollectionFactory;
        $this->customizers = $customizers;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Product $product): OfferCollection
    {
        /** @var OfferCollection $collection */
        $collection = $this->offerCollectionFactory->create();

        foreach ($this->customizers as $customizer) {
            $customizer->customize($product, $collection);
        }

        return $collection;
    }
}
