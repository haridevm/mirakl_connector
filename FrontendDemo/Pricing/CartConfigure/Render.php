<?php
declare(strict_types=1);

namespace Mirakl\FrontendDemo\Pricing\CartConfigure;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Mirakl\FrontendDemo\Helper\Quote as QuoteHelper;
use Mirakl\FrontendDemo\Model\Offer\RenderProduct\CreatorInterface;

class Render extends \Magento\Catalog\Pricing\Render
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var CreatorInterface
     */
    private $renderProductCreator;

    /**
     * @param Context          $context
     * @param Registry         $registry
     * @param QuoteHelper      $quoteHelper
     * @param CreatorInterface $renderProductCreator
     * @param array            $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QuoteHelper $quoteHelper,
        CreatorInterface $renderProductCreator,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->quoteHelper = $quoteHelper;
        $this->renderProductCreator = $renderProductCreator;
    }

    /**
     * @inheritdoc
     */
    protected function getProduct()
    {
        $product = parent::getProduct();

        if (!$quoteItemId = $this->getQuoteItemId()) {
            return $product;
        }

        $quoteItem = $this->quoteHelper->getQuote()->getItemById($quoteItemId);
        $offer = $quoteItem ? $quoteItem->getData('offer') : null;

        if (!$offer) {
            return $product;
        }

        return $this->renderProductCreator->create($offer, $product);
    }

    /**
     * @return int|null
     */
    private function getQuoteItemId()
    {
        return $this->getRequest()->getParam('id');
    }
}
