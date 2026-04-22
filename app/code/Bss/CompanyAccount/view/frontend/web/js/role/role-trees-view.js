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
    'jquery/ui',
    window.jsTree
], function ($) {
    "use strict";

    $.widget("bss.sub_role", {
        options: {
            treeInitData: {},
            editFormSelector: '',
            resourceFieldName: 'role_type[]',
            checkboxVisible: true
        },
        _create: function () {
            this.element.jstree({
                plugins: ['checkbox'],
                checkbox: {
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    three_state: true,
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    visible: this.options.checkboxVisible
                },
                core: {
                    data: this.options.treeInitData,
                    themes: {
                        dots: false
                    }
                }
            });
            this._bind();
        },

        /**
         * @private
         */
        _destroy: function () {
            this.element.jstree('destroy');
        },

        /**
         * @private
         */
        _bind: function () {
            this.element.on('select_node.jstree', $.proxy(this._selectChildNodes, this));
            this.element.on('deselect_node.jstree', $.proxy(this._deselectChildNodes, this));
            this.element.on('changed.jstree', $.proxy(this._changedNode, this));
            let formData = $('#form-validate');
            formData.submit(function () {
                if (formData.valid()) {
                    $('#save-submit').prop('disabled', true);
                }
            });
            formData.bind("invalid-form.validate", function () {
                $('#save-submit').prop('disabled', false)
            });
        },

        /**
         * @param {Event} event
         * @param {Object} selected
         * @private
         */
        _selectChildNodes: function (event, selected) {
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            selected.instance.open_node(selected.node);
            if (selected.node.children.length > 0) {
                selected.node.children.forEach(function (id) {
                    var selector = '[id="' + id + '"]';

                    selected.instance.select_node(
                        selected.instance.get_node($(selector), false)
                    );
                });
            }
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        },

        /**
         * @param {Event} event
         * @param {Object} selected
         * @private
         */
        _deselectChildNodes: function (event, selected) {
            selected.node.children.forEach(function (id) {
                var selector = '[id="' + id + '"]';

                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                selected.instance.deselect_node(
                    selected.instance.get_node($(selector), false)
                );
                // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            });
        },

        /**
         * Add selected resources to form to be send later
         *
         * @param {Event} event
         * @param {Object} selected
         * @private
         */
        _changedNode: function (event, selected) {
            //Disable role 12 (Place order request)
            if (selected.selected.includes('13') || selected.selected.includes('11')) {
                selected.selected = selected.selected.filter(function (ele) {
                    return ele != '12';
                });
                $('#12_anchor').removeClass('jstree-clicked').addClass('jstree-unchecked').css(
                    'color', '#c7bcbc'
                ).prop('checked', false);
            } else {
                $('#12_anchor').css('color', 'black');
            }

            //Enable rule 7 when rule 13 is enabled
            if (selected.selected.includes('13') && !selected.selected.indexOf('7')) {
                $('#7_anchor').removeClass('jstree-unchecked').addClass('jstree-clicked').children().prop('checked', true);
                selected.selected = selected.selected.push('7');
            }
            var form = $(this.options.editFormSelector),
                fieldName = this.options.resourceFieldName,
                items = selected.selected.concat($(this.element).jstree('get_undetermined'));

            if (this.options.editFormSelector === '') {
                return;
            }
            form.find('input[name="' + this.options.resourceFieldName + '"]').remove();
            items.map(function (id) {
                $('<input>', {
                    type: 'hidden',
                    name: fieldName,
                    value: id
                }).appendTo(form);
            });
        }
    });
    return $.bss.sub_role;
});
