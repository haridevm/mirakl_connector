<?php
namespace Mirakl\Core\Plugin\Model\Quote\Cart;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;

class CartTotalRepositoryPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var TotalsExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param TotalsExtensionFactory  $extensionFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        TotalsExtensionFactory $extensionFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param   CartTotalRepository $subject
     * @param   TotalsInterface     $result
     * @return  TotalsInterface
     */
    public function afterGet(
        CartTotalRepository $subject,
        TotalsInterface $result,
        $cartId
    ) {
        $quote = $this->quoteRepository->getActive($cartId);

        if (null === $result->getExtensionAttributes()) {
            $extensionAttributes = $this->extensionFactory->create();
            $result->setExtensionAttributes($extensionAttributes);
        }

        /** @var \Magento\Quote\Api\Data\TotalsExtension $extensionAttributes */
        $extensionAttributes = $result->getExtensionAttributes();

        $extensionAttributes->setMiraklShippingExclTax($quote->getMiraklShippingExclTax());
        $extensionAttributes->setMiraklShippingInclTax($quote->getMiraklShippingInclTax());

        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
