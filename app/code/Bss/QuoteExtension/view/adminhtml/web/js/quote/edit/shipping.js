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
    "jquery"
], function ($) {
    'use strict';

    $.widget('quoteextension.shipping', {
        options: {
            selector: {
                input: undefined,
                price: undefined,
                submit: undefined
            },
            method: undefined
        },

        _create: function () {
            var self = this;
            $(self.element).click(function (event) {
                event.preventDefault();
                $(self.options.selector.input).toggle();
                $(self.options.selector.price).toggle();
            });

            $(self.options.selector.submit).click(function (event) {
                event.preventDefault();

                /** @see AdminQuote.setShippingMethodWithPrice */
                window.quote.setShippingMethodWithPrice(
                    self.options.method,
                    $(self.options.selector.input + " input").val()
                );
            });
        }
    });

    return $.quoteextension.shipping;
});