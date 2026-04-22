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
    'jquery'
], function ($) {
    "use strict";

    $.widget("bss.subUser", {
        options: {
            beSubmitted: false
        },
        _create: function () {
            this._bind();
        },
        _bind: function () {
            let formData = $('#form-validate');
            $(document).ready(function () {
                formData.submit(function (e) {
                    if (formData.valid()) {
                        $(this).find(':submit').prop('disabled', true);
                    }
                });
                formData.bind("invalid-form.validate", function (e) {
                    $(this).find(':submit').prop('disabled', false);
                });
            });
        }
    });
    return $.bss.subUser;
});
