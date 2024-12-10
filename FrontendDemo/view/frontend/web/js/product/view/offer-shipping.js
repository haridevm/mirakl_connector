/**
 * JS component to load shipping details for Mirakl offers based
 * on customer address in Product Detail Page (PDP)
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'domReady!'
], function ($, Component, modal) {
    'use strict';
    return Component.extend({
        /**
         * Initialize component
         *
         * @param config
         */
        initialize: function (config) {
            this._super();
            let self = this;

            $(".shipping-options-link").each(function () {
                let offerId = $(this).data("offer-id");
                let target = '#modal-content-' + offerId
                self.initModal(target);
                $(this).click(function () {
                    $(target).modal('openModal');
                });
            });

            let offerShippingUrl = config.offerShippingUrl;
            let addressShippingMethodsUrl = config.addressShippingMethodsUrl;
            let skus = document.product_skus;

            self.loadOfferShippingSections(skus, offerShippingUrl, addressShippingMethodsUrl);
        },

        /**
         * Initialize offer shipping methods modal
         *
         * @param target
         */
        initModal: function (target) {
            let modalOptions = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: $.mage.__('Shipping Options'),
                buttons: []
            };
            modal(modalOptions, $(target));
        },

        /**
         * Load offer shipping sections
         *
         * @param skus
         * @param offerShippingUrl
         * @param addressShippingMethodsUrl
         */
        loadOfferShippingSections: function (skus, offerShippingUrl, addressShippingMethodsUrl) {
            let self = this;
            $.ajax({
                url: offerShippingUrl,
                data: {
                    product_skus: skus
                },
                type: 'GET',
                dataType: 'json'
            }).done(function (data) {
                self.updateSections(data.result, addressShippingMethodsUrl);
            });
        },

        /**
         * Update offer shipping details sections after PDP load
         *
         * @param offersShippingData
         */
        updateSections: function (offersShippingData, reloadMethodsUrl) {
            let self = this;
            let offerShippingSections = $('.offer-shipping-details');
            let bestOfferShippingSections = $('.offer-shipping.best-offer-shipping');
            if (offersShippingData !== undefined && offersShippingData !== false) {
                let showShippingCell = false;
                offerShippingSections.each(function () {
                    let offerId = $(this).data('shipping-offer-id');
                    let shippingEstimation = offersShippingData[offerId];
                    if (shippingEstimation !== undefined) {
                        if (shippingEstimation['customer_addresses'] !== undefined) {
                            // Update customer addresses section
                            let selector = '.customer-shipping-address[data-offer-id="' + offerId + '"]';
                            let inputName = 'address' + offerId;
                            $(selector).each(function () {
                                $(this).html(shippingEstimation['customer_addresses']);
                                $('input[type="radio"][name="' + inputName + '"]').click(function () {
                                    let offerId = $(this).data('offer-id');
                                    let productSku = $(this).data('product-sku');
                                    let addressId = $(this).val();
                                    self.updateModal(reloadMethodsUrl, offerId, productSku, addressId);
                                });
                            });
                        }
                        if (shippingEstimation['offer_shipping'] !== undefined && shippingEstimation['offer_shipping'].length !== 0) {
                            showShippingCell = true;
                            $(this).html(shippingEstimation['offer_shipping']);
                        }
                    }
                });
                if (showShippingCell) {
                    $('.cell.label.shipping').removeAttr('style');
                    $('.cell.shipping-details').each(function () {
                        $(this).show();
                    });
                    $('.shipping-options-link').each(function () {
                        $(this).show();
                    });
                }
                bestOfferShippingSections.each(function () {
                    let offerId = $(this).data('shipping-offer-id');
                    let shippingEstimation = offersShippingData[offerId];
                    if (shippingEstimation !== undefined) {
                        if (shippingEstimation['best_offer_shipping'] !== undefined) {
                            $(this).html(shippingEstimation['best_offer_shipping']);
                        }
                    }
                });
            } else {
                $('.offer-shipping.shipping-options').each(function () {
                    $(this).remove();
                });
            }
            bestOfferShippingSections.each(function () {
                $(this).show();
            });
        },

        /**
         * Update modal with offer shipping method details
         *
         * @param url
         * @param offerId
         * @param sku
         * @param addressId
         */
        updateModal: function (url, offerId, sku, addressId) {
            let selector = '.offer-shipping-details[data-shipping-offer-id="' + offerId + '"]';
            $.ajax({
                url: url,
                showLoader: true,
                data: {
                    product_sku: sku,
                    offer_id: offerId,
                    address_id: addressId
                },
                type: 'GET',
                dataType: 'json'
            }).done(function (data) {
                if (data.result === false) {
                    $(selector).html('<strong>' + $.mage.__('Shipping methods cannot be loaded.') + '</strong>')
                } else {
                    $(selector).html(data.result)
                }
            }).fail(function (jqXHR, status, err) {
                $(selector).html('<strong>' + $.mage.__('An error occurred while loading shipping methods.') + '</strong')
            })
        },
    });
});