<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Plugin\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Mirakl\FrontendDemo\Helper\Offer as OfferHelper;
use Mirakl\FrontendDemo\Model\Offer\RenderProduct\CreatorInterface;

class ListProductPlugin
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
     * @var Product
     */
    protected $currentProduct;

    /**
     * @param OfferHelper      $offerHelper
     * @param CreatorInterface $renderProductCreator
     */
    public function __construct(
        OfferHelper $offerHelper,
        CreatorInterface $renderProductCreator
    ) {
        $this->offerHelper = $offerHelper;
        $this->renderProductCreator = $renderProductCreator;
    }

    /**
     * @param ListProduct $subject
     * @param \Closure    $proceed
     * @return AbstractCollection
     */
    public function aroundGetLoadedProductCollection(ListProduct $subject, \Closure $proceed)
    {
        /** @var AbstractCollection $productCollection */
        $productCollection = $proceed();
        foreach ($productCollection as $product) {
            if ($offer = $this->offerHelper->getBestOffer($product)) {
                $product->setData('main_offer', $offer);
            }
        }

        return $productCollection;
    }

    /**
     * @param ListProduct $subject
     * @param \Closure    $proceed
     * @param Product     $product
     * @return string
     */
    public function aroundGetProductPrice(ListProduct $subject, \Closure $proceed, Product $product)
    {
        $renderProduct = $product;

        if ($offer = $this->offerHelper->getBestOffer($product)) {
            $renderProduct = $this->renderProductCreator->create($offer, $product);
        }

        return $proceed($renderProduct);
    }

    /**
     * @param ListProduct $subject
     * @param \Closure    $proceed
     * @param string      $name
     * @return string
     */
    public function aroundGetBlockHtml(ListProduct $subject, \Closure $proceed, $name)
    {
        $html = $proceed($name);

        if ($name == 'formkey' && $this->currentProduct && !$this->currentProduct->isComposite()) {
            // Add an hidden field for best offer if possible to allow add to cart
            $offer = $this->offerHelper->getBestOffer($this->currentProduct);
            if ($offer) {
                $html .= sprintf('<input type="hidden" name="offer_id" value="%d">', $offer->getId());
            }
        }

        return $html;
    }

    /**
     * @param ListProduct $subject
     * @param \Closure    $proceed
     * @param Product     $product
     * @return string
     */
    public function aroundGetProductDetailsHtml(ListProduct $subject, \Closure $proceed, Product $product)
    {
        /** @var \Mirakl\FrontendDemo\Block\Product\Offer\Summary $blockOfferSummary */
        $blockOfferSummary = $subject->getLayout()
            ->createBlock(\Mirakl\FrontendDemo\Block\Product\Offer\Summary::class);
        $blockOfferSummary->setProduct($product);

        $this->currentProduct = $product; // used for aroundGetBlockHtml(), it avoids heavy override for offer_id

        return $blockOfferSummary->toHtml() . $proceed($product);
    }
}
