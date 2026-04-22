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
require(
    [
        'jquery',
        'mage/translate',
        'jquery/validate'],
    function ($) {
        $.validator.addMethod(
            'validate-b2b-url',
            function (v) {
                // eslint-disable-next-line no-alert
                return /^[A-Za-z0-9_.~][A-Za-z0-9_.~-]*[A-Za-z0-9_.~]$/.test(v);
            },
            $.mage.__('Special characters are not allowed')
        );
    }
);
