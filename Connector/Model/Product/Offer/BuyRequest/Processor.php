<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\BuyRequest;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\SecurityViolationException;
use Mirakl\Connector\Model\Product\Offer\BuyRequest\Validator\ValidatorInterface;
use Mirakl\Connector\Model\Product\Offer\CustomOption;

class Processor implements ProcessorInterface
{
    /**
     * @var CustomOption\CreatorInterface
     */
    private $customOptionCreator;

    /**
     * @var ValidatorInterface[]
     */
    private $validators = [];

    /**
     * @param CustomOption\CreatorInterface $customOptionCreator
     * @param array                         $validators
     */
    public function __construct(
        CustomOption\CreatorInterface $customOptionCreator,
        array $validators = []
    ) {
        $this->customOptionCreator = $customOptionCreator;
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function process(DataObject $buyRequest, Product $product): void
    {
        if (!$offerId = $buyRequest->getData('offer_id')) {
            return;
        }

        $superAttribute = $buyRequest->getData('super_attribute');

        if ($product->getTypeId() === Configurable::TYPE_CODE || empty($superAttribute)) {
            // Add the Mirakl offer as a custom option on configurable product
            // or on the simple product if it's not a variant
            $offer = $this->customOptionCreator->create($product, (int) $offerId);

            foreach ($this->validators as $validator) {
                if (!$validator->validate($buyRequest, $product, $offer)) {
                    throw new SecurityViolationException(__('An error happened when adding the product to '
                        . 'the cart. Please reload the page and try again.'));
                }
            }

            $product->addCustomOption('mirakl_offer', $offer->toJson());
        }
    }
}
