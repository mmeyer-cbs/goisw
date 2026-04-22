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
 * @category  BSS
 * @package   Bss_CustomerAttributes
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

/* global AdminOrder */
define([
    'jquery',
    'Magento_Sales/order/create/scripts'
], function (jQuery) {
    'use strict';

    AdminOrder.prototype.syncAddressField = function (container, fieldName, fieldValue) {
        var syncName;

        if (this.isBillingField(fieldName)) {
            syncName = fieldName.replace('billing', 'shipping');
        }

        $(container).select('[name="' + syncName + '"]').each(function (element) {
            if (~['input', 'textarea', 'select'].indexOf(element.tagName.toLowerCase())) {
                if (element.type === "checkbox") {
                    element.checked = fieldValue.checked;
                } else if (element.type === "file") {
                    element.files = fieldValue.files;
                } else {
                    element.value = fieldValue.value;
                }
            }
        });
    };
});
