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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'mage/validation'
    ], function (quote, ko, Component, $) {
        'use strict';
        var companyCredit = window.checkoutConfig.companyCredit;
        var canPlaceOrder = 1;
        var allowExceed = 0;

        // eslint-disable-next-line no-shadow
        return function (Component) {
            return Component.extend
            (
                {
                    /**
                     * Get message exceed credit limit
                     */
                    getExceedCreditLimitMessage: function () {
                        return $.mage.__('You will exceed credit limit with this order.');
                    },

                    /**
                     * Enable button place order.
                     */
                    allowExceedYes: function () {
                        window.allowExceed = 1;
                        this.canPlaceOrder(1);
                        return true;
                    },

                    /**
                     * Disable button place order.
                     */
                    allowExceedNo: function () {
                        window.allowExceed = 0;
                        this.canPlaceOrder(0);
                        return true;
                    },

                    /**
                     * Check enable module.
                     */
                    enableModule: function () {
                        if(companyCredit !== undefined && companyCredit.enableModule !== undefined) {
                            return companyCredit.enableModule;
                        }
                        return 0;
                    },

                    /**
                     * Get available credit.
                     */
                    getAvailableCredit: function () {
                        if(companyCredit !== undefined && companyCredit.availableCreditCurrency !== undefined) {
                            return companyCredit.availableCreditCurrency;
                        }
                        return 0;
                    },


                    /** @inheritdoc */
                    initObservable: function () {
                        var totals = quote.getTotals();

                        if(companyCredit !== undefined && companyCredit.availableCredit !== undefined
                            && companyCredit.availableCredit - totals()["base_grand_total"] < 0
                        ) {
                            canPlaceOrder = 0;
                        }
                        this._super()
                            .observe({
                                purchaseOrderNumber: null,
                                canPlaceOrder: canPlaceOrder,
                                allowExceed : allowExceed
                            });

                        return this;
                    },

                    /** Check place order */
                    checkPlaceOrder: function () {
                        var totals = quote.getTotals();

                        if(companyCredit !== undefined && companyCredit.availableCredit !== undefined
                            && companyCredit.availableCredit - totals()["base_grand_total"] < 0
                        ) {
                            if(window.allowExceed !== 1) {
                                canPlaceOrder = 0;
                                this.canPlaceOrder(0);
                            }
                            this.allowExceed(companyCredit.allowExceed);
                        } else {
                            this.allowExceed(0);
                            this.canPlaceOrder(1);
                            canPlaceOrder = 1;
                        }
                        return false;
                    },
                }
            );
        };
    }
);
