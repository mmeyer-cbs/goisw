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
define(function () {
    'use strict';

    var bssCompanyAccountRuleCheck = {
        initConfig: function () {
            this._super();
            if (window.checkoutConfig.cant_create_address) {
                this.addressOptions = this.addressOptions.filter(item => item.customerAddressId !== null);
            }
        }
    };
    return function (target) {
        return target.extend(bssCompanyAccountRuleCheck);
    };
});
