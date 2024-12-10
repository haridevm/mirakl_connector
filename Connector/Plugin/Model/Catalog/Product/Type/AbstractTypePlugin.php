<?php
declare(strict_types=1);

namespace Mirakl\Connector\Plugin\Model\Catalog\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Mirakl\Connector\Model\Product\Offer\BuyRequest;

class AbstractTypePlugin
{
    /**
     * @var BuyRequest\ProcessorInterface
     */
    private $buyRequestProcessor;

    /**
     * @param BuyRequest\ProcessorInterface $buyRequestProcessor
     */
    public function __construct(BuyRequest\ProcessorInterface $buyRequestProcessor)
    {
        $this->buyRequestProcessor = $buyRequestProcessor;
    }

    /**
     * Add Mirakl offer if present in request
     *
     * @param DataObject $buyRequest
     * @param Product $product
     * @return void
     */
    private function addMiraklOffer(DataObject $buyRequest, Product $product): void
    {
        $this->buyRequestProcessor->process($buyRequest, $product);
    }

    /**
     * @param AbstractType $abstractType
     * @param DataObject $buyRequest
     * @param Product $product
     */
    public function beforeProcessConfiguration(AbstractType $abstractType, DataObject $buyRequest, Product $product)
    {
        $this->addMiraklOffer($buyRequest, $product);
    }

    /**
     * @param AbstractType $abstractType
     * @param DataObject $buyRequest
     * @param Product $product
     */
    public function beforePrepareForCartAdvanced(AbstractType $abstractType, DataObject $buyRequest, Product $product)
    {
        $this->addMiraklOffer($buyRequest, $product);
    }
}
