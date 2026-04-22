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
        'ko',
        'jquery'
    ],
    function (ko, $) {
        "use strict";

        /**
         * A model that helps with selecting KO models
         */
        return {
            /** Selectors: BEGIN */
            shippingSelector: '.checkout-shipping-address',
            billingSelector: '.billing-address-form',
            /** Selectors: END */

            /**
             * Get the billing KO model
             */
            getBillingModel: function () {
                return ko.dataFor($(this.billingSelector)[0]);
            },

            /**
             * Get the shipping KO model
             */
            getShippingModel: function () {
                return ko.dataFor($(this.shippingSelector)[0]);
            },

            /**
             * Check if shipping address is available
             * @returns {boolean}
             */
            hasShippingAddress: function () {
                return $(this.shippingSelector).length > 0;
            },

            /**
             * Check if billing address is available
             * @returns {boolean}
             */
            hasBillingAddress: function () {
                return $(this.billingSelector).length > 0;
            },
        };
    }
);
