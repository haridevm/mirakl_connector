define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($) {
    'use strict';
    $.widget('mage.confirmSendOrder', {
        options: {
            url:     null,
            message: null,
            modal:  null
        },

        /**
         * @protected
         */
        _create: function () {
            this._prepareDialog();
        },

        /**
         * Show modal
         */
        showDialog: function () {
            this.options.dialog.html(this.options.message).modal('openModal');
        },

        /**
         * Redirect to send order URl
         */
        redirect: function () {
            window.location = this.options.url;
        },

        /**
         * Prepare modal
         */
        _prepareDialog: function () {
            var self = this;
            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>').modal({
                type: 'popup',
                modalClass: 'mirakl-send-order-popup',
                title: $.mage.__('Send order to Mirakl'),
                buttons: [{
                    text: $.mage.__('Send'),
                    'class': 'action-primary',
                    /** @inheritdoc */
                    click: function () {
                        self.redirect();
                    }
                }]
            });
        }
    });

    return $.mage.confirmSendOrder;
});
