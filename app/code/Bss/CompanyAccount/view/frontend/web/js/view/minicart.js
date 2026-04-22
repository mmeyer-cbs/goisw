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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/storage',
    'jquery'
], function (customerData, urlBuilder, storage, $) {
    'use strict';

    return function (Component) {
        return Component.extend({
            checkRoleOrder: function () {
                customerData.reload(['bssSubuserRoleOrder'], true);
                return customerData.get('bssSubuserRoleOrder')._latestValue.check_order_role;
            },
            isApprovedQuote: function () {
                let sections = ['bssSubuserRoleOrder'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
                return Boolean(customerData.get('bssSubuserRoleOrder')._latestValue.approved_quote);
            },
            backToCart: function () {
                $.ajax({
                    url: urlBuilder.build('companyaccount/checkout/backtoquote'),
                    showLoader: true,
                    success : function (response) {
                        $('#bss-back-to-cart').hide();
                        eval(response.output);
                        customerData.reload(['bssSubuserRoleOrder'], true);
                    } ,
                    error : function () {
                        console.log('Error happens. Try again.');
                    }
                });
            }
        });
    }
});

