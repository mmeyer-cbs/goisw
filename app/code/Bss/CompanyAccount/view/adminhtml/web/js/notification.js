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
    'mage/template',
], function ($, mageTemplate) {
    "use strict";

    var bssSuccessMessage = {
        options: {
            templates: {
                reset_pw_subuser_request_success: '<div data-role="messages" id="messages">' +
                    '<% if (data.messageErrorEmail) { %>'+
                    '<div class="message message-error error">' +
                    '<div data-ui-id="messages-message-error"><%- data.messageErrorEmail %></div>' +
                    '</div>' +
                    '<% } %>' +
                    '<div class="messages"><div class="message message-success success">' +
                    '<div data-ui-id="messages-message-success"><%- data.message %></div></div>' +
                    '</div></div>'
            }
        },

        add: function (data) {
            if (data.reset_pw_subuser_request_success) {
                var template = this.options.templates.reset_pw_subuser_request_success,
                    message = mageTemplate(template, {
                        data: data
                    }),
                    messageContainer;

                if (typeof data.insertMethod === 'function') {
                    data.insertMethod(message);
                } else {
                    messageContainer = data.messageContainer || this.placeholder;
                    $(messageContainer).prepend(message);
                }

                return this;
            }
            return this._super(data);
        }
    };

    return function (targetWidget) {
        $.widget('mage.notification', targetWidget, bssSuccessMessage); // the widget alias should be like for the target widget

        return $.mage.notification; //  the widget by parent alias should be returned
    };
});
