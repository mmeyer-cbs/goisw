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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Bss_StoreCredit/js/action/set-storecredit'
    ],
    function ($, ko, Component, setStoreCreditAction) {
        'use strict';

        var data = window.checkoutConfig,
            isApplied = ko.observable(false),
            totalAmountCustomer = ko.observable(data.storeCreditTotal),
            cancel = ko.observable(0),
            amount = ko.observable(null),
            isDisplayed = false;
        if (data.storeCreditQuote || data.storeCreditQuote === 0) {
            isDisplayed = true;
            amount(parseFloat(data.storeCreditQuote));
        }
        if (data.storeCreditQuote) {
            isApplied(true);
        }

        return Component.extend({
            defaults: {
                template: 'Bss_StoreCredit/payment/storecredit'
            },
            amount : amount,
            totalAmountCustomer: totalAmountCustomer,
            isApplied: isApplied,

            apply: function () {
                if (this.validate()) {
                    setStoreCreditAction(amount, isApplied, totalAmountCustomer);
                }
            },

            cancel: function () {
                if (this.validate()) {
                    setStoreCreditAction(cancel, isApplied, totalAmountCustomer);
                }
            },

            validate: function () {
                var form = '#bss-store-credit-form';

                return $(form).validation() && $(form).validation('isValid');
            },

            isDisplayed: function () {
                if (isDisplayed) {
                    return true;
                }
            }
        });
    }
);
