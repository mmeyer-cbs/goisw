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

define([
    "jquery",
    "Magento_Customer/js/customer-data"
], function ($, customerData) {
    "use strict";

    $.widget('bss.storecredit', {
        options: {
            bssStoreCreditValue : '#bss-store-credit-value',
            bssStoreCreditRemove : '#remove-bss-store-credit',
            bssStoreCreditApply : 'button.action.bss-store-credit-apply',
            bssStoreCreditCancel : 'button.action.bss-store-credit-cancel',
            bssstoreCreditShipping : '#shipping_amount-bss-store-credit',
            bssstoreCreditTax : '#tax_amount-bss-store-credit'
        },
        _create: function () {
            this.storeCreditValue = $(this.options.bssStoreCreditValue);
            this.storeCreditRemove = $(this.options.bssStoreCreditRemove);
            this.storeCreditShipping = $(this.options.bssstoreCreditShipping);
            this.storeCreditTax = $(this.options.bssstoreCreditTax);

            $(this.options.bssStoreCreditApply).on('click', $.proxy(function () {
                this.storeCreditValue.attr('data-validate', '{required:true}');
                this.storeCreditRemove.attr('value', '0');
                var cartData = customerData.get('cart-data')();
                if (cartData['totals']) {
                    if(cartData['totals']['shipping_amount']) {
                        this.storeCreditShipping.attr('value', cartData['totals']['shipping_amount']);
                    }
                    if(cartData['totals']['shipping_amount']) {
                        this.storeCreditTax.attr('value', cartData['totals']['tax_amount']);
                    }
                }
                $(this.element).validation().submit();
            }, this));

            $(this.options.bssStoreCreditCancel).on('click', $.proxy(function () {
                this.storeCreditValue.removeAttr('data-validate');
                this.storeCreditRemove.attr('value', '1');
                this.element.submit();
            }, this));
        }
    });

    return $.bss.storecredit;
});
