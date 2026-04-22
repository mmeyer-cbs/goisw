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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomPricing/js/model/source/priceType',
    'mageUtils'
], function (ko, Abstract, priceType, utils) {
    'use strict';

    return Abstract.extend({
        defaults: {
            changeSymbol: "",
            percentSymbol: null,
            currencies: null
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this
                ._super()
                .observe(['changeSymbol', 'percentSymbol', 'addbefore']);
        },

        /**
         * Change symbol
         */
        handleTypeChanges: function (value) {
            switch (parseInt(value)) {
                case priceType['absolute_price']:
                    this.changeSymbol("");
                    this.percentSymbol('')
                    break;
                case priceType['increase_fixed_price']:
                    this.changeSymbol("+");
                    this.percentSymbol('')
                    break;
                case priceType['decrease_fixed_price']:
                    this.changeSymbol("-");
                    this.percentSymbol('')
                    break;
                case priceType['increase_percent_price']:
                    this.percentSymbol('%');
                    this.changeSymbol("+");
                    break;
                case priceType['decrease_percent_price']:
                    this.percentSymbol('%');
                    this.changeSymbol("-");
                    break;
                default:
                    break;
            }
        },

        /**
         * Handling currency base on website selection
         *
         * @param {Number} value
         */
        handleAddbefore: function (value) {
            if (this.currencies && this.currencies[value] !== undefined) {
                if (!ko.isObservable(this.addbefore)) {
                    this.observe('addbefore');
                }
                this.addbefore(this.currencies[value]);
            }
        }
    });
});
