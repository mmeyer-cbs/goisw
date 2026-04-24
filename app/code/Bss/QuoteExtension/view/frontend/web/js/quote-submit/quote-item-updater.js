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
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/place-order',
        'Bss_QuoteExtension/js/quote-submit/model/resource-url-manager',
        'Magento_Ui/js/model/messageList',
    ],
    function ($, quote, placeOrderService, resourceUrlManagerModel, globalMessageList) {
        "use strict";
        /**
         * Bidding widget for placing a bid on the product detail page.
         */
        $.widget('bss.updateQuote', {

            /**
             * The element options:
             * - Item ID is set in the element
             * - sessionProductKey is the key used on the session
             */
            options: {
                itemId: 0,
                sessionProductKey: undefined
            },

            /**
             * Add all the events on create
             *
             * @private
             */
            _create: function () {
                this.bindInputCheck();
                this.toggleDisabled();
            },

            /**
             * Update the session JS data on keyup
             */
            bindInputCheck: function () {
                var self = this;
                $(this.element).on('change', function (e) {
                    self.saveData(this);
                });
            },

            /**
             * Save the new price to the session
             */
            saveData: function (element) {
                var quoteRequestUrl, quoteData;
                $('.ajax-quote-model').show();
                quoteData = {
                    cartId: quote.getQuoteId(),
                    form_key: $.mage.cookies.get('form_key'),
                    itemId: this.options.itemId,
                    sessionProductKey: this.options.sessionProductKey,
                    value: $(element).val()
                };

                quoteRequestUrl = resourceUrlManagerModel.getUrlForUpdateQuote(quote, quoteData);
                return placeOrderService(quoteRequestUrl, quoteData, globalMessageList).done(
                    function (result) {
                        $('.ajax-quote-model').hide();
                        if (result.error) {
                            globalMessageList.addErrorMessage({message: result.error});
                        }
                    }
                );
            },

            /**
             * Toggle disabled
             */
            toggleDisabled: function () {
                $(this.element).prop('disabled', function (i, v) {
                    return !v;
                });
            }

        });

        return $.bss.updateQuote;
    }
);
