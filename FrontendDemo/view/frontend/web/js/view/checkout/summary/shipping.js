/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/shipping'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        getShippingMethodTitle: function () {
            return '';
        },

        /**
         * @override
         */
        isCalculated: function () {
            return this._super() || this.totals()['mirakl_shipping_excl_tax'] > 0;
        },

        /**
         * @override
         */
        getIncludingValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }

            price = Math.abs(this.totals()['shipping_incl_tax']);

            if ('mirakl_shipping_incl_tax' in this.totals()) {
                price += Math.abs(this.totals()['mirakl_shipping_incl_tax']);
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @override
         */
        getExcludingValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }

            price = Math.abs(this.totals()['shipping_amount']);

            if ('mirakl_shipping_excl_tax' in this.totals()) {
                price += Math.abs(this.totals()['mirakl_shipping_excl_tax']);
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @override
         */
        getValue: function () {
            return this.getExcludingValue();
        },
    });
});
