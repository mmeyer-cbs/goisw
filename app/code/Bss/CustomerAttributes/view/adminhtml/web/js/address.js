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
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';
    return function (config) {
        var finalDependentArr = JSON.parse(config.finalDependentArr);
        var attrValues = [];
        if (config.attrValues !== '') {
            attrValues = JSON.parse(config.attrValues);
        }
        $.each(finalDependentArr, function (finalKey, finalValue) {
            $(".field-" + finalValue).attr("hidden", true);
            $("#order-billing_address_" + finalValue).children('option').attr("hidden", true);
            $("#order-billing_address_" + finalValue).children('option').filter(":selected").show();
        });
        $.each(attrValues, function (attrKey, attrValue) {

            var obj = JSON.parse(attrValue['dependents_data']);

            $.each(attrValue, function () {
                $("#order-billing_address_" + attrValue['attr_code']).change(function () {
                    var data = $(this).val();
                    var count = 0;
                    var elementShow = [];
                    // eslint-disable-next-line max-nested-callbacks
                    $.each(obj, function (index, value) {
                        var attrDV = 'attribute-values';
                        if (data.includes(value[attrDV].value)) {
                            var dependentValue = value[attrDV].dependent_attribute.dependent_attribute_value;
                            var showUp = value[attrDV].dependent_attribute.value;

                            count++;
                            $(".field-" + showUp).show();
                            $(".field-" + showUp).attr('value', count);
                            elementShow.push(value[attrDV].dependent_attribute.value);
                            if (typeof dependentValue !== 'undefined') {
                                var optionParent = value[attrDV].dependent_attribute.value;
                                var options = $("#order-billing_address_" + optionParent).children('option');
                                // eslint-disable-next-line max-nested-callbacks
                                var values = $.map(options, function (option) {
                                    return option.value;
                                });
                                const allValue = values.concat(dependentValue);
                                // eslint-disable-next-line no-use-before-define
                                var optionValue = duplicatedArray(allValue);

                                if (typeof optionValue !== 'undefined') {
                                    // eslint-disable-next-line max-nested-callbacks
                                    $.each(optionValue, function (optionI, optionV) {
                                        if (values.includes(optionV)) {
                                            $('option[value="' + optionV + '"]').show();
                                        }
                                    });
                                    // eslint-disable-next-line max-len
                                    var notShow = allValue.filter(n => !optionValue.includes(n))
                                    $.each(notShow, function (optionI, optionV) {
                                        if (optionV !== '') {
                                            $('option[value="' + optionV + '"]').hide();
                                        }
                                    });
                                }
                            }
                        } else {
                            count--;
                            $(".field-" + value[attrDV].dependent_attribute.value).hide();
                            if (typeof $(".field-" + value[attrDV].dependent_attribute.value).attr('value') !== 'undefined') {
                                var display = $(".field-" + value[attrDV].dependent_attribute.value).attr('value');
                                $(".field-" + value[attrDV].dependent_attribute.value).attr('value', display + count);
                            }
                            elementShow.push(value[attrDV].dependent_attribute.value);
                        }
                    });

                    function duplicatedArray($val)
                    {
                        // eslint-disable-next-line max-nested-callbacks
                        return $val.reduce(function (acc, el, i, arr) {
                            if (arr.indexOf(el) !== i && acc.indexOf(el) < 0) {
                                acc.push(el);
                            }
                            return acc;
                        }, []);
                    }

                    // eslint-disable-next-line max-nested-callbacks
                    // eslint-disable-next-line max-len
                    //Show element duplicate by many other
                    if (typeof duplicatedArray(elementShow) !== 'undefined' && $(".field-" + duplicatedArray(elementShow)).attr('value') === 1) {
                        $(".field-" + duplicatedArray(elementShow)).show();
                    }
                });
            });
        });
    };
});
