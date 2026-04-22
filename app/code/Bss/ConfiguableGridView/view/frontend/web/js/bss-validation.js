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
 * @package    Bss_ConfiguableGridView
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function() {
        $.validator.addMethod(
            'bss-validate-qty-increment',
            function (qty, element, qtyIncrements) {
                //Skip check increment qty
                if (typeof qtyIncrements === 'undefined' || qty <= 0) {
                    return true;
                }

                //Fix duplicate error message.
                if (element && element.parentElement.querySelector("div.mage-error")) {
                    element.parentElement.querySelector("div.mage-error").remove();
                }

                //Logic check increment qty of magento, func resolveModulo() in file lib/web/mage/validation.js
                var divideEpsilon = 10000,
                    epsilon,
                    remainder;

                while (qtyIncrements < 1) {
                    qty *= 10;
                    qtyIncrements *= 10;
                }

                epsilon = qtyIncrements / divideEpsilon;
                remainder = qty % qtyIncrements;

                if (Math.abs(remainder - qtyIncrements) < epsilon ||
                    Math.abs(remainder) < epsilon) {
                    remainder = 0;
                }

                var result = (remainder === 0.0);

                if (result === false) {
                    this.itemQtyErrorMessage = $.mage.__('You can buy this product only in quantities of %1 at a time.').replace('%1', qtyIncrements);
                }

                return result;
            },
            function () {
                return this.itemQtyErrorMessage;
            }
        );
    }
});
