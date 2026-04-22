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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/template',
    'jquery-ui-modules/widget',
    'Magento_Bundle/js/price-bundle'
], function ($, mageTemplate) {
    'use strict';
    return function (widget) {
        $.widget('mage.ProductSummary', widget, {
            /**
             * Overwrite summary after render hide price action.
             *
             * @param {String} key
             * @param {String} optionIndex
             * @private
             */
            _renderOptionRow: function (key, optionIndex) {
                this._super(key, optionIndex);

                if (
                    !document.getElementById('product-addtoquote-button') &&
                    this.cache.currentElement.hidePrice
                ) {
                    var arrayHidePrice = Object.values(this.cache.currentElement.hidePrice);
                    if (arrayHidePrice && arrayHidePrice.includes(parseInt(optionIndex))) {
                        //Disable input & Overwrite value
                        document.getElementById('bundle-option-' + this.cache.currentKey + '-qty-input').disabled = true;
                        document.getElementById('bundle-option-' + this.cache.currentKey + '-qty-input').value = 0;

                        //Overwrite summary
                        var templateOverwrite;
                        templateOverwrite = this.element
                            .closest(this.options.summaryContainer)
                            .find(this.options.templates.optionBlock)
                            .html();
                        templateOverwrite = mageTemplate(templateOverwrite.trim(), {
                            data: {
                                _quantity_: 0,
                                _label_: this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex].name
                            }
                        });
                        this.cache.summaryContainer
                            .find(this.options.optionSelector)
                            .html(templateOverwrite);
                    }
                }
            }
        });

        return $.mage.ProductSummary;
    }
});
