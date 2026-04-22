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
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function ($, Component, customerData) {
    'use strict';
    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            customerData.reload(['bssSubuserRoleOrder'], true);
            if (this.cantCreateAddress) {
                $.async(`.secondary button.action.add,
                    .box-shipping-address a.action.edit,
                    .box-billing-address .box-actions .action.edit,
                    .box-billing-address a.action.edit,
                    .actions-toolbar .primary .action.add.primary`, function (element) {
                    $(element).remove();
                });
            }
            if (window.checkoutConfig && window.checkoutConfig.cant_create_address) {
                $.async('.new-address-popup', function (element) {
                    $(element).remove();
                });
            }
        }
    });
});
