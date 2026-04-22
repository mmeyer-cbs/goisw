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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(function () {
    'use strict';

    var mixin = {
        disableRegister: window.authenticationPopup.disableRegister,
        enableB2bRegister: window.authenticationPopup.enableB2bRegister,
        disableB2bRegularRegister: window.authenticationPopup.disableB2bRegularRegister,
        b2bRegisterUrl: window.authenticationPopup.b2bRegisterUrl,
        shortcutLinkText: window.authenticationPopup.shortcutLinkText,
        defaults: {
            template: 'Bss_B2bRegistration/authentication-popup'
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});