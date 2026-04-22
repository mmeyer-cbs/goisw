/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_QuoteExtension
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Bss_ConfiguableGridView/js/swatch/configurable'
], function ($, _, customerData) {
    'use strict';

    return function (config) {
        var cartData = customerData.get('quote'),
            itemIdSelector = '#product_addtocart_form [name="item"]',
            productIdSelector = '#product_addtocart_form [name="product"]',
            configurableTableSelector = '#bss_configurablegridview table',
            itemId = $(itemIdSelector).val() || null,
            productId = $(productIdSelector).val() || null;
        if (itemId && cartData()) {
            cartData = cartData();
            if (cartData.items) {
                var selectedItem = cartData.items.filter(function (item) {
                    return item.product_id === productId;
                }),
                    $configurableTable = $(configurableTableSelector);
                $configurableTable.find('tr.item-info').each(function (index, item) {
                    selectedItem.forEach(function (productItem) {
                        var selectedOptions = productItem.options,
                            isNotThisItem = selectedOptions.some(function (option) {
                                return !$(item).find(`[attribute-id="${option.option_id}"][attribute-value="${option.option_value}"]`).length;
                            });
                        if (!isNotThisItem) {
                            var $row = $(item),
                                rowIndex = $row.attr('index'),
                                $qtyInput = $row.find(`#super_group_qty_${rowIndex}`),
                                $quoteItemId = $row.find(`input[name="quote_item_id[${rowIndex}]"]`);
                            if ($quoteItemId.length) {
                                $quoteItemId.val(productItem.item_id);
                            }
                            if ($qtyInput.length) {
                                $qtyInput.val(productItem.qty).change();
                            }
                        }
                    });
                });
            }
        }
    }
});
