define([
    'jquery',
    'underscore',
    'baseConfigurable'
], function ($, _) {
    'use strict';

    $.widget('mage.configurable', $.mage.configurable, {
        _configureElement: function (element) {
            this._FilterAllOffers();
            this._super(element);
        },

        _FilterAllOffers: function () {
            if (!$('#marketplace_offers')) {
                return;
            }

            var elements = _.toArray(this.options.settings);
            var allowedProducts = [];

            _.each(elements, function (element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config;

                if (config && config.allowedProducts.length > 0) {
                    if (allowedProducts.length === 0) {
                        allowedProducts = config.allowedProducts;
                    } else {
                        allowedProducts = allowedProducts.filter(function (n) {
                            return config.allowedProducts.indexOf(n) !== -1;
                        });
                    }
                }
            }, this);

            var $widget = this,
                $productOffers = $('#product-offers').hide(),
                $offersTab = $('#tab-label-marketplace_offers').hide(),
                $productOffersChoicebox = $('#product-offers-choicebox').hide(),
                $productOffersChoiceboxTable = $('#choicebox-product-offers-list'),
                $offerListElements = $('#product-offers-list').find('tr.offer').hide(),
                $offerChoiceBoxElements = $productOffersChoiceboxTable.find('tr.offer').hide(),
                nbOfferChoiceBoxElementsMax = $productOffersChoiceboxTable.attr('data-max-offers');

            var results = _.pick($widget.options.spConfig.optionPrices, function (v, i) {
                return allowedProducts.indexOf(i) !== -1;
            });

            _.each(results, function (result) {
                result = result.offerData;

                $offerListElements.filter('.sku-' + result.productSku).show();
                $offerChoiceBoxElements.filter('.sku-' + result.productSku).show();

                // Show offers content if the tab is hidden
                if (!$offersTab.is(':visible')) {
                    $offersTab.show();
                    $offersTab.click();
                    $productOffers.show();
                }
            });

            var offerId = '';
            if (_.size(results) == 1) {
                var firstResult = _.first(_.values(results));
                if (firstResult.offerData && firstResult.offerData.type === 'offer') {
                    offerId = firstResult.offerData.offerId;
                }

                $productOffersChoicebox.show();
                var $offerChoiceBoxElementsVisible = $offerChoiceBoxElements.filter(':visible');
                var offerChoiceBoxElementsVisibleLength = $offerChoiceBoxElementsVisible.length;

                if (offerChoiceBoxElementsVisibleLength < 1 || offerChoiceBoxElementsVisibleLength === 1 && firstResult.offerData.type === 'offer') {
                    $productOffersChoicebox.hide();
                } else {
                    $productOffersChoicebox.show();

                    // we must hide the others and recalculate the right elements visible
                    if (offerChoiceBoxElementsVisibleLength > nbOfferChoiceBoxElementsMax) {
                        $offerChoiceBoxElements.filter(':visible:gt(' + (nbOfferChoiceBoxElementsMax - 1) + ')').hide();
                    }

                    // define "X offer from Y" label
                    var $shown = $offerListElements.filter(':visible');
                    $('.product-offers-summary-number').html($shown.length);

                    var firstPrice = $shown.first();
                    var price = parseFloat(firstPrice.find('[data-price-amount]').first().attr('data-price-amount'));
                    $shown.each(function () {
                        var linePrice = parseFloat($(this).find('[data-price-amount]').first().attr('data-price-amount'));
                        if (linePrice < price) {
                            price = linePrice;
                            firstPrice = $(this);
                        }
                    });

                    if (firstPrice.is('.offer-1p')) {
                        $('.switch > span.price').html(firstPrice.find('.price-container .price').first().text());
                    } else {
                        $('.switch > span.price').html(firstPrice.find('.offer-price .price').first().text());
                    }
                }
            }

            this.element.find('#product-addtocart-button').attr('data-offer', offerId);
        }
    });

    return $.mage.configurable;
});
