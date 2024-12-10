/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'uiComponent',
    'jquery'
], function (ko, Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mirakl_Api/test-connection',
            connectionFailedMessage: 'Connection test failed.',
            fieldIdPrefix: '#mirakl_api_general_',
            url: '',
            success: false,
            message: '',
            visible: false
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super().observe(['success', 'message', 'visible']);

            return this;
        },

        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.messageClass = ko.computed(function () {
                return 'message-validation message message-' + (this.success() ? 'success' : 'error');
            }, this);

            if (!this.success()) {
                this.showMessage(false, this.connectionFailedMessage);
            }
        },

        /**
         * @param {bool} success
         * @param {String} message
         */
        showMessage: function (success, message) {
            this.message(message);
            this.success(success);
            this.visible(true);
        },

        /**
         * Send request to server to test connection to Mirakl API and display the result
         */
        testConnection: function () {
            let authMethod = this.getField('auth_method');
            let data = {
                api_url: this.getField('api_url').val(),
                auth_method: authMethod.val()
            };

            switch (authMethod.val()) {
                case 'api_key':
                    data.api_key = this.getField('front_key_api_key').val();
                    break;
                case 'access_token':
                    data.access_token = this.getField('bearer_access_token').val();
                    break;
                case 'oauth2':
                    data.client_id = this.getField('oauth2_client_id').val();
                    data.client_secret = this.getField('oauth2_client_secret').val();
                    data.auth_url = this.getField('oauth2_auth_url').val();
                    break;
                default:
                    this.showMessage(false, this.connectionFailedMessage);
            }

            this.visible(false);

            $.ajax({
                type: 'POST',
                url: this.url,
                dataType: 'json',
                data: data,
                success: function (response) {
                    this.showMessage(response.success === true, response.message);
                }.bind(this),
                error: function () {
                    this.showMessage(false, this.connectionFailedMessage);
                }.bind(this)
            });
        },

        /**
         * @param {String} name
         * @returns {*|jQuery|HTMLElement}
         */
        getField: function (name) {
            return $(this.fieldIdPrefix + name);
        }
    });
});
