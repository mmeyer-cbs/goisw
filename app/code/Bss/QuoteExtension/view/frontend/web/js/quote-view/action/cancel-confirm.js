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
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
], function ($, confirmation) {
    "use strict";
    $.widget('bss.cancelConfirm', {
        options: {
            cancelButton: '.quote-cancel'
        },

        _create: function () {
            let self = this,
                cancelForm = $(self.options.cancelButton).closest('form');
            $(self.options.cancelButton).on('click', function (event) {
                event.preventDefault();
                confirmation({
                    title: $.mage.__('Quote Cancel'),
                    content: $.mage.__('Are you sure you want to cancel this quote?'),
                    actions: {
                        confirm: function () {
                             cancelForm.submit();
                        },
                        cancel: function () {
                            return false;
                        },
                        always: function () {

                        }
                    }
                });
            });
        }
    });

    return $.bss.cancelConfirm;
});