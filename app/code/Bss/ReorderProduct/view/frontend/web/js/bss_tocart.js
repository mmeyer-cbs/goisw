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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Bss_ReorderProduct/js/bss_tocart',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'Bss_ReorderProduct/js/catalog-add-to-cart'
], function ($) {
    'use strict';

    $.widget('bss.bss_tocart', {
        options: {
            moduleEnabled: false
        },
        _create: function () {
            'use strict';
            var self = this;
            $('#product_addtocart_form').mage('validation', {
                radioCheckboxClosest: '.nested',
                submitHandler: function (form) {
                    var widget = $(form).reorderCatalogAddToCart({
                        bindSubmit: false,
                        moduleEnabled: !!+self.options.moduleEnabled
                    });
                    widget.reorderCatalogAddToCart('submitForm', $(form));
                    return false;
                }
            });
            $('#ajax-goto a').click(function (e) {
                e.preventDefault();
                window.top.location.href = $(this).attr('href');

                return false;
            });
        }
    });
    return $.bss.bss_tocart;
});
