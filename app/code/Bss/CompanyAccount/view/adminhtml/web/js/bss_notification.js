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
    'use strict';
    return {
        bssNotification: function (response, clearAfter = 3000) {
            if (Array.isArray(response)) {
                response = response[0];
            }
            $('body').notification('clear')
                .notification('add', {
                    reset_pw_subuser_request_success: !response.error,
                    error: response.error,
                    message: response.message,
                    messageErrorEmail: response.messageErrorEmail,
                    insertMethod: function (message) {
                        var $wrapper = $('<div/>').html(message);

                        $('.page-main-actions').after($wrapper);
                    }
                });
            if (clearAfter) {
                setTimeout(function () {
                    $('body').notification('clear');
                }, clearAfter);
            }
        }
    };
});
