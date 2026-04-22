/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Magento_Ui/js/form/form',
    'ko',
    'uiRegistry',
    'Bss_QuoteExtension/js/quote-submit/action/place-quote',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/quote',
    'Bss_QuoteExtension/js/quote-submit/model/quote-checkout-model-selector',
    'Bss_QuoteExtension/js/full-screen-loader',
    'Magento_Ui/js/model/messageList'
], function (
    $,
    Component,
    ko,
    registry,
    placeQuoteAction,
    setShippingInformationAction,
    shippingService,
    quote,
    selector,
    fullScreenLoader
) {
    'use strict';

    return Component.extend({
        /**
         * get customer
         */
        getCustomer: window.checkoutConfig.customerData,

        /**
         * Show the login button
         */
        showLoginButton: null,

        /**
         * Show the request button
         */
        showRequestButton: null,

        initialize: function () {
            var self = this;
            this._super();

            this.initLoginButton();
            this.initRequestButton();
        },

        /**
         * A function to request the quote.
         * If the shipping address and quotations fields are valid
         * then the quote will be requested.
         */
        validateQuote: function () {
            if (!this.validateNotLogin()) {
                return false;
            }
            if (window.checkoutConfig.isRequiredAddress && !quote.isVirtual()) {
                var shippingAddressComponent = registry.get('block-submit.steps.shipping-step.shippingAddress');
                if (shippingAddressComponent.validateShippingInformation()){
                    fullScreenLoader.startLoader();
                    setShippingInformationAction().done(function () {
                        placeQuoteAction(true);
                    });
                }
            } else {
                fullScreenLoader.startLoader();
                placeQuoteAction(true);
            }
        },

        /**
         * Scroll the page to the error
         * @return void
         */
        scrollToError: function () {
            var errorElement = $('._error').get(0);
            if (typeof errorElement != undefined) {
                var offset = $(errorElement).offset();
                if (typeof offset != 'undefined') {
                    $('html, body').animate({scrollTop: $(errorElement).offset().top}, 500);
                }

            }
        },

        /**
         * Check if the customer is logged in
         */

        isLoginCustomer: function() {
            if(this.getCustomer.firstname)
                return true;
            return false;
        },

        /**
         * Init the login button
         */
        initLoginButton: function () {
            var self = this;

            self.showLoginButton = ko.computed(function () {
                return !self.isLoginCustomer()
            });
        },

        /**
         * Init the request button
         */
        initRequestButton: function () {
            var self = this;

            self.showRequestButton = ko.computed(function () {
                return (!window.checkoutConfig.inValidAmount && window.checkoutConfig.addToQuote)
            });
        },

        /**
         * Load login-popup.js using data-mage-init
         */
        setLoginModalEvents: function() {
            $('.login').trigger('contentUpdated');
        },

        validateNotLogin: function() {
            var validate = true;
            if (!this.isLoginCustomer()) {
                var loginFormSelector = 'form[data-role=email-with-possible-login]';

                $(loginFormSelector).validation();
                var emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();
                    validate = false;
                }

                var personalInformation = 'form[data-role= persional-information]';
                $(personalInformation).validation();

                var firstnameValidationResult = Boolean($(personalInformation + ' input[name=personal-information-firstname]').valid());
                if (!firstnameValidationResult) {
                    $(personalInformation + ' input[name=personal-information-firstname]').focus();
                    validate = false;
                }

                var lastnameValidationResult = Boolean($(personalInformation + ' input[name=personal-information-lastname]').valid());
                if (!lastnameValidationResult) {
                    $(personalInformation + ' input[name=personal-information-lastname]').focus();
                    validate = false;
                }
            }

            return validate;
        }
    });
});
