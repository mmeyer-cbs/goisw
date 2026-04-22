/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txtpossible_onepage_checkout
 *
 * @category   BSS
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'mage/url',
    'Magento_Customer/js/section-config'
], function ($, urlBuilder, sectionConfig) {
    'use strict';
    return function (config, element) {
        $(element).click(function (event) {
            event.preventDefault();
            $(element).attr('disabled', true);
            var url = urlBuilder.build('companyaccount/order/sendrequest');
            sectionConfig.getAffectedSections(url);
            $.ajax({
                url: url,
                type: 'POST',
                success: function () {
                    window.location.href = urlBuilder.build('sales/order/history/tab/waiting');
                },
                error: function (xhr, status, errorThrown) {
                    console.log('Error happens. Try again.');
                }
            });
        });
    };
});
