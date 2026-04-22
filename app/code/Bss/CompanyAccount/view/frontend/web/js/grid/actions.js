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
    'Magento_Ui/js/modal/confirm'
], function ($, confirm) {
    'use strict';
    $.widget('bss.company_account_grid_action', {
        options: {
            url: '',
            method: 'post',
            triggerEvent: 'click',
            useConfirm: true,
            cfTitle: '',
            cfContent: ''
        },

        _create: function () {
            this._bind();
        },

        _bind: function () {
            let self = this;
            self.element.on(self.options.triggerEvent, function (event) {
                self.executeAction($(this));
            });
        },

        executeAction: function (selector) {
            let self = this;
            if (this.options.useConfirm) {
                confirm({
                    title: this.options.cfTitle,
                    content: this.options.cfContent,
                    actions: {
                        confirm: function () {
                            self._ajaxSubmit(selector);
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            } else {
                this._ajaxSubmit(selector);
            }
        },

        _ajaxSubmit: function (selector) {
            let self = this,
                formKey = $('input[name="form_key"]').val() || null;
            $.ajax({
                url: self.options.url,
                type: self.options.method,
                dataType: 'json',
                data: {form_key: formKey},
                beforeSend: function() {
                    $('body').trigger('processStart');
                }
            }).done(function (res) {
                if (res) {
                    if (res.remove_row) {
                        let $row = selector.closest('tr');
                        $row.remove();
                    }
                }
            }).always(function () {
                $('body').trigger('processStop');
            });
        }
    });
    return $.bss.company_account_grid_action;
});
