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

define(
    [
        'mage/url',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils'
    ],
    function (url, customer, urlBuilder, utils) {
        "use strict";

        /**
         * A model for handling the action URL's
         */
        return {

            /**
             * Get the update quote session URL.
             * @param quote
             * @returns string
             * @param params
             */
            getUrlForRedirectOnSuccess: function (params) {
                var urls = {
                    'guest': 'quoteextension/quote/success',
                    'customer': 'quoteextension/quote/success'
                };
                return this.getUrl(urls, params, false);
            },

            /**
             * Get the update quote session URL.
             * @param quote
             * @returns string
             * @param params
             */
            getUrlForUpdateQuote: function (quote, params) {
                var urls = {
                    'guest': '/quoteextension/quote_ajax/updateQuote',
                    'customer': '/quoteextension/quote_ajax/updateQuote'
                };
                return this.getUrl(urls, params, false);
            },

            /**
             * Get the create quote URL.
             * @param quote
             * @returns string
             * @param params
             */
            getUrlForPlaceQuote: function (quote, params) {
                var urls = {
                    'guest': '/quoteextension/guest/place-quote',
                    'customer': '/quoteextension/mine/place-quote'
                };
                return this.getUrl(urls, params, true);
            },

            /**
             * Get url for service
             * @return string
             */
            getUrl: function (urls, urlParams, apiCall) {
                var newUrl;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    newUrl = urls['default'];
                } else {
                    newUrl = urls[this.getCheckoutMethod()];
                }

                if (apiCall) {
                    return urlBuilder.createUrl(newUrl, urlParams);
                } else {
                    return url.build(newUrl) + this.prepareParams(urlParams);
                }
            },

            /**
             * Get the checkout method
             * @returns {string}
             */
            getCheckoutMethod: function () {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            },

            /**
             * Format params
             *
             * @param {Object} params
             * @returns {string}
             */
            prepareParams: function (params) {
                var result = '?';

                _.each(params, function (value, key) {
                    result += key + '=' + value + '&';
                });

                return result.slice(0, -1);
            }
        };
    }
);
