/*global define,alert*/
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-save-processor'
    ],
    function ($, quote, shippingSaveProcessor) {
        'use strict';

        return function () {
            // Add additional methods to payload
            if (!quote.shippingAddress().extensionAttributes) {
                quote.shippingAddress().extensionAttributes = {};
            }
            quote.shippingAddress().extensionAttributes.additional_methods = $.param($('input[name^="shipping_method"]:checked'));

            return shippingSaveProcessor.saveShippingInformation(quote.shippingAddress().getType());
        };
    }
);
