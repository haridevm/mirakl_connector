<?php

declare(strict_types=1);

namespace Mirakl\GraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\SetShippingMethodsOnCart;
use Mirakl\Connector\Model\Quote\Updater as QuoteUpdater;

class SetShippingMethodsOnCartPlugin
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @param GetCartForUser $getCartForUser
     * @param QuoteUpdater   $quoteUpdater
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        QuoteUpdater $quoteUpdater
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->quoteUpdater = $quoteUpdater;
    }

    /**
     * Apply Mirakl shipping methods to Marketplace items and update Mirakl shipping info
     * (shipping method codes, shipping fees, ...) before Magento applies shipping methods
     * to operator items and re-collect quote totals.
     *
     * @param SetShippingMethodsOnCart $subject
     * @param Field                    $field
     * @param ContextInterface         $context
     * @param ResolveInfo              $info
     * @param array|null               $value
     * @param array|null               $args
     * @return array
     */
    public function beforeResolve(
        SetShippingMethodsOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (isset($args['input']['mp_shipping_methods']) && count($args['input']['mp_shipping_methods'])) {
            if (empty($args['input']['cart_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
            }

            $maskedCartId = $args['input']['cart_id'];
            $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
            $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

            $offersShippingTypes = [];
            foreach ($args['input']['mp_shipping_methods'] as $shippingMethod) {
                $offersShippingTypes[$shippingMethod['offer_id']] = $shippingMethod['shipping_type_code'];
            }

            $this->quoteUpdater->updateOffersShippingTypes($offersShippingTypes, $cart, false, true);
        }

        return [$field, $context, $info, $value, $args];
    }
}
