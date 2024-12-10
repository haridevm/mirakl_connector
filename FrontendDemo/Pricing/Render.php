<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Pricing;

use Magento\Catalog\Pricing\Render as BaseCatalogPricingRender;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Mirakl\FrontendDemo\Helper\Offer as OfferHelper;
use Mirakl\FrontendDemo\Model\Offer\RenderProduct\CreatorInterface;

/**
 * @method string getPriceRender()
 * @method string getPriceTypeCode()
 */
class Render extends BaseCatalogPricingRender
{
    /**
     * @var OfferHelper
     */
    protected $offerHelper;

    /**
     * @var CreatorInterface
     */
    protected $renderProductCreator;

    /**
     * @param Context          $context
     * @param Registry         $registry
     * @param OfferHelper      $frontendOfferHelper
     * @param CreatorInterface $renderProductCreator
     * @param array            $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OfferHelper $frontendOfferHelper,
        CreatorInterface $renderProductCreator,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->offerHelper = $frontendOfferHelper;
        $this->renderProductCreator = $renderProductCreator;
    }

    /**
     * @inheritdoc
     */
    protected function getProduct()
    {
        $product = parent::getProduct();

        if ($this->offerHelper->getBestOperatorOffer($product)) {
            return $product;
        }

        if (!$offer = $this->offerHelper->getBestOffer($product)) {
            return $product;
        }

        return $this->renderProductCreator->create($offer, $product);
    }
}
