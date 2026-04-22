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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(['jquery'], function ($) {
    'use strict';

    return function() {
        $.validator.addMethod(
            "bss-validate-integer",
            function(value, element) {
                if (($.isNumeric(value) && Math.floor(value) == value) || value === "") {
                    return true;
                }
                return false;
            },
            $.mage.__("A positive or negative non-decimal number please")
        );
    }
});
