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
    window.jsTree
], function ($) {
    "use strict";

    $.widget("bss.sub_role", {
        options: {
            beSubmitted: false,
            treeInitData: {},
            treeInitSelectedData: {}
        },
        _create: function () {
            this.element.jstree({
                plugins: ['themes', 'json_data', 'ui', 'crrm', 'types', 'vcheckbox'],
                vcheckbox: {
                    'two_state': true,
                    'real_checkboxes': true,

                    /**
                     * @param {*} n
                     * @return {Array}
                     */
                    'real_checkboxes_names': function (n) {
                        return ['role_type[]', $(n).data('id')];
                    }
                },
                'json_data': {
                    data: this.options.treeInitData
                },
                ui: {
                    'select_limit': 0
                },
                types: {
                    'types': {
                        'disabled': {
                            'check_node': false,
                            'uncheck_node': false
                        }
                    }
                }
            }).bind('change_state.jstree', function () {
                var roleApproveOrder = $('[data-id="13"]');
                if (roleApproveOrder.attr('class').search("jstree-checked") > 0) {
                    $('[data-id="7"]').removeClass('jstree-unchecked').addClass('jstree-checked').children().prop('checked', true);
                }
                if (roleApproveOrder.attr('class').search("jstree-checked") > 0
                    || $('[data-id="11"]').attr('class').search("jstree-checked") > 0
                ) {
                    $('[data-id="12"]').removeClass('jstree-checked').addClass('jstree-unchecked').children().css(
                        'color', 'gray'
                    ).prop('checked', false);
                } else {
                    $('[data-id="12"]').children('a').css('color', 'black');
                }
            });
            this._bind();
        },

        /**
         * @private
         */
        _bind: function () {
            this.element.on('loaded.jstree', $.proxy(this._checkNodes, this));
            this.element.on('click.jstree', 'a', $.proxy(this._checkNode, this));
            let formData = $('#form-validate');
            formData.submit(function () {
                if (formData.valid()) {
                    $(this).find(':submit').prop('disabled', true);
                }
            });
            formData.bind("invalid-form.validate", function () {
                $(this).find(':submit').prop('disabled', false)
            });
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _checkNode: function (event) {
            event.stopPropagation();
            this.element.jstree(
                'change_state',
                event.currentTarget,
                this.element.jstree('is_checked', event.currentTarget)
            );
        },

        /**
         * @private
         */
        _checkNodes: function () {
            this.options.treeInitSelectedData = this.arrayRemove(this.options.treeInitSelectedData, "");
            let defaultCheck = '';
            if (this.options.treeInitSelectedData.length > 0) {
                defaultCheck = '[data-id="0"],';
            }
            var $items = $(defaultCheck + '[data-id="' + this.options.treeInitSelectedData.join('"],[data-id="') + '"]');

            $items.removeClass('jstree-unchecked').addClass('jstree-checked');
            $items.children(':checkbox').prop('checked', true);
            $('#save-submit').prop('disabled', false);
        },

        arrayRemove: function (arr, value) {
            return arr.filter(function (ele) {
                return ele != value;
            });
        }
    });
    return $.bss.sub_role;
});
