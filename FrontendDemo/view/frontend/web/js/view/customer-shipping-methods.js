define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function ($, Component, customerData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            var self = this;
            customerData.reload(['customer-shipping-methods']).done(function () {
                var dataCallback = customerData.get('customer-shipping-methods');
                var data = dataCallback();
                self.updateSections(data);
            });
        },

        /**
         * Update shipping estimation sections on PDP
         * @param data
         */
        updateSections: function (data) {
            var offersShippingData = data['customer-shipping-methods'];
            var offerShippingSections = $('.offer-shipping-details');
            var bestOfferShippingSections = $('.offer-shipping.best-offer-shipping');
            if (offersShippingData !== undefined && offersShippingData !== false) {
                var showShippingCell = false;
                offerShippingSections.each(function () {
                    var offerId = $(this).data('shipping-offer-id');
                    var shippingEstimation = offersShippingData[offerId];
                    if (shippingEstimation !== undefined) {
                        if (shippingEstimation['offer_shipping'] !== undefined && shippingEstimation['offer_shipping'].length !== 0) {
                            showShippingCell = true;
                            var estimationByType = shippingEstimation['offer_shipping'];
                            var estimation = '';
                            estimationByType.forEach(function (el) {
                                estimation = estimation + '<li>' + el + '</li>';
                            })
                            $(this).html(estimation);
                        }
                    }
                });
                if (showShippingCell) {
                    $('.cell.shipping').each(function () {
                        $(this).show();
                    });
                }
                bestOfferShippingSections.each(function () {
                    var offerId = $(this).data('shipping-offer-id');
                    var shippingEstimation = offersShippingData[offerId];
                    if (shippingEstimation !== undefined) {
                        if (shippingEstimation['best_offer_shipping'] !== undefined) {
                            $(this).html(shippingEstimation['best_offer_shipping']);
                        }
                    }
                });
            }
            bestOfferShippingSections.each(function () {
                $(this).show();
            });
        },
    });
});