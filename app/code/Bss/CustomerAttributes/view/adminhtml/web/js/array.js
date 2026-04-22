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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    'use strict';
    return function (config) {
        $(document).ready(function () {
            $('.action-add').prop('disabled', true);

            //display dependent values
            var select = $('select[class="dependent_attribute"]');
            $.each(select, function () {
                var selectId = this.id;
                if (selectId === 'boolean' || selectId === 'checkboxs' ||
                    selectId === 'radio' || selectId === 'select'
                    || selectId === 'multiselect') {
                    $(this).next(".dependent_values").show();
                    $(this).closest("tr").find("select.attribute").attr('multiple', true);
                    $(this).closest("tr").find("select.attribute").attr('required', true);
                } else {
                    $(this).next(".dependent_values").hide();
                    $(this).closest("tr").find("select.attribute").attr('multiple', false);
                    $(this).closest("tr").find("select.attribute").attr('required', false);
                }
            });

            var value = $('select[name="frontend_input"]').val();
            var i = config.index;
            var attrValues = config.attrValues
            var obj = JSON.parse(attrValues);


            if (value === 'boolean' || value === 'checkboxs' || value === 'radio' || value === 'select') {
                $('.action-add').prop('disabled', false);
            }

            $(document).on('change', '.dependent_attribute', function () {
                var optionName = $(this).find(":selected").attr("name");
                var attrcode = $(this).children(":selected").attr("id")
                var that = $(this)
                $(this).next("label").remove();
                if (optionName === 'boolean' || optionName === 'checkboxs' ||
                    optionName === 'radio' || optionName === 'select'
                    || optionName === 'multiselect') {
                    $(this).next(".dependent_values").show();
                    $(this).closest("tr").find("select.attribute").attr('multiple', true);
                    $(this).closest("tr").find("select.attribute").attr('required', true);
                } else {
                    $(this).next(".dependent_values").hide();
                    $(this).closest("tr").find("select.attribute").attr('multiple', false);
                    $(this).closest("tr").find("select.attribute").attr('required', false);
                }
                $(this).closest("tr").find("select.attribute option").remove();
                $.each(obj, function (index, value) {
                    $(that).children(":selected").val(attrcode);
                    if (index == attrcode) {
                        $.each(value, function (i, v) {
                            if (v.label.trim() !== '') {
                                $(that).closest("tr").find("select.attribute").append(`<option value="${v.value}">
                                  ${v.label}
                                </option>`);
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.action-add', function () {
                // eslint-disable-next-line max-len
                i++;
                $('select[name="relation_data[attribute-values][value]"]').attr("name", "relation_data[" + i + "][attribute-values][value]");
                $('select[name="relation_data[attribute-values][dependent_attribute][value]"]').attr("name", "relation_data[" + i + "][attribute-values][dependent_attribute][value]");
                $('select[name="relation_data[attribute-values][dependent_attribute][dependent_attribute_value]"]').
                    // eslint-disable-next-line max-len
                    attr("name", "relation_data[" + i + "][attribute-values][dependent_attribute][dependent_attribute_value][]");
            });

            $(document).on('click', '.action-delete', function () {
                $(this).parents('tr').remove();
            });
        });
    };
});
