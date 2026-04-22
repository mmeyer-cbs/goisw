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
        var attrValues = config.attrValues;
        var displayValues = config.displayValues;
        var finalDependentArr = [];
        if (config.finalDependentArr != "") {
            finalDependentArr = JSON.parse(config.finalDependentArr);
        }
        var triggerDependents = [];
        $.each(finalDependentArr, function (finalKey, finalValue) {
            $("#" + finalValue).attr("hidden", true);
            $("#" + finalValue).attr("value", 0);
            $("#" + finalValue).children('div').children('.field.choice').attr("hidden", true);
            if (!$("#" + finalValue).is(':visible') && $("#" + finalValue).is('hidden')) {
                $("#" + finalValue).find("input").removeAttr("checked");
            }
        });
        if (attrValues!==''){
            var obj = JSON.parse(attrValues);
        }
        var data = [];
        var av = 'attribute-values'
        $.each(obj, function (index, value) {
            var render = JSON.parse(value.value.dependents_data)
            Object.keys(render).forEach(function (key) {
                var renderValue = render[key][av].value;
                var optionRender = render[key][av].dependent_attribute.dependent_attribute_value;

                if (!$('#' + renderValue).is(":hidden")
                    && $('input[value="' + renderValue + '"]').is(':checked')
                    || $('option[id="' + renderValue + '"]').is(':selected')
                ) {
                    if ($('input[value="' + renderValue + '"]').is(':checked')) {
                        triggerDependents.push('input[value="' + renderValue + '"]');
                    }
                    if ($('option[id="' + renderValue + '"]').is(':selected')) {
                        triggerDependents.push('option[id="' + renderValue + '"]');
                    }
                    if ($('#' + renderValue).children(":first").is('input')) {
                        $('.field.' + render[key][av].dependent_attribute.value).attr('value', 1);
                    }
                    $.each(optionRender, function (ork, orv) {
                        $('#' + orv).show();
                        if ($('#' + renderValue).children(":first").is('input')) {
                            $('#' + orv).attr('value', 1);
                        }
                    });
                }
            });

            $.each(value, function (i, v) {
                //get children Id
                const divIds = $('#info > div').map(function () {
                    return this.id
                }).get();
                data.push(v)
                $.each(divIds, function (keys, values) {

                    if (v.attr_id === values) {
                        var result = data.filter(object => {
                            return object.attr_id === values
                        })
                        $.each(result, function (resKey, resValue) {
                            var dependentData = JSON.parse(resValue.dependents_data)
                            $.each(dependentData, function (dpKey, dpValue) {
                                $.each(dpValue, function (attrIndex, attrValue) {
                                    var display = attrValue.value
                                    $.each(attrValue, function (dpAttrIndex, dpAttrValue) {
                                        var name = dpAttrValue.value;
                                        if (displayValues === '0') {
                                            function showUp(value)
                                            {
                                                if (typeof name !== 'undefined') {
                                                    if (typeof value !== 'undefined') {
                                                        value++;
                                                        $('.field.' + name).attr("value", value);
                                                        $.each($('.field.' + name).find("option"), function (optionKey, optionInfo) {
                                                            if ($('#' + optionInfo.id).attr('selected') == "selected") {
                                                                if (!dpAttrValue.dependent_attribute_value.includes(optionInfo.id)) {
                                                                    $('#' + optionInfo.id).attr('selected', false);
                                                                }
                                                            }
                                                        })
                                                    }

                                                    $('.field.' + name).attr("value", value)
                                                    $('.field.' + name).removeClass("hidden");
                                                    $('.field.' + name).attr("hidden", false);
                                                    $('.field.' + name).show();
                                                    $('#' + name).find("option").show();

                                                    $.each(dpAttrValue.dependent_attribute_value, function (key, info) {
                                                        $('#' + info).show();
                                                        $('#' + info).attr('name',)
                                                        if (typeof $('#' + info).attr('value') === 'undefined' || $('#' + info).attr('value') === '0') {
                                                            $('#' + info).attr('value', value)
                                                        } else {
                                                            if ($('#' + info).prop('tagName') === 'DIV') {
                                                                $('#' + info).attr('value', value + 1)
                                                            } else {
                                                                $('#' + info).attr('name', value)
                                                            }
                                                        }
                                                    })
                                                    $.each($('#' + name).find("option"), function (optionKey, optionInfo) {
                                                        var check =$('#' + optionInfo.id).attr('name');
                                                        if (typeof check !== 'undefined' && check !== '0') {
                                                            $('#' + optionInfo.id).css("display", "block");
                                                        } else {
                                                            $('#' + optionInfo.id).css("display", "none");
                                                        }
                                                        $('#' + name).find(":selected").css("display", "block");
                                                    })
                                                }
                                            }

                                            function notShow(value)
                                            {

                                                if (typeof name !== 'undefined') {
                                                    if (typeof value !== 'undefined') {
                                                        value--;
                                                        $('.field.' + name).attr("value", value);
                                                    }
                                                    if (typeof $('.field.' + name).attr("name") !== 'undefined') {
                                                        if ($('.field.' + name).attr("value") == 0 &&
                                                            $('.field.' + name).attr("name") === '0' ||
                                                            $('.field.' + name).attr("value") < 0
                                                        ) {
                                                            $('.field.' + name).hide();
                                                        } else {
                                                            $('.field.' + name).show();
                                                        }
                                                    } else {
                                                        if ($('.field.' + name).attr("value") == 0
                                                        ) {
                                                            $('.field.' + name).hide();
                                                        } else {
                                                            $('.field.' + name).show();
                                                        }
                                                    }
                                                    $.each(dpAttrValue.dependent_attribute_value, function (key, info) {
                                                        $('#' + name).find("option").filter("option[id='" + info + "']").hide();
                                                        if (typeof value !== 'undefined') {
                                                            if ($('#' + info).prop('tagName') === 'DIV') {
                                                                var test = $('#' + info).attr('value')
                                                                $('#' + info).attr('value', test - value - 1)
                                                                if ($('#' + info).attr('value') < 0) {
                                                                    $('#' + info).attr('value', 0)
                                                                }
                                                                if ($('#' + info).attr('value') === '0' || $('.field.' + name).css('display') == 'none') {
                                                                    $('#' + info).css("display", "none");
                                                                    $('input[value="' + info + '"]').prop("checked", false);
                                                                    $('input[value="' + info + '"]').prop("selected", false);
                                                                }
                                                            } else {
                                                                var test = $('#' + info).attr('name')
                                                                $('#' + info).attr('name', test - value - 1)
                                                                if ($('#' + info).attr('name') < 0) {
                                                                    $('#' + info).attr('name', 0)
                                                                }
                                                                if ($('#' + info).attr('name') === '0') {
                                                                    $('#' + info).css("display", "none");
                                                                }
                                                            }
                                                        } else {
                                                            $('#' + info).css("display", "none");
                                                        }
                                                    })
                                                }
                                            }
                                            //Change display YesNo-> 0 1
                                            if (display == "Yes"){
                                                display = 1;
                                            }
                                            if (display == "No"){
                                                display = 0;
                                            }
                                            //Radio box dependent
                                            if ($('#' + display).is("div") === true) {
                                                if ($('#' + display).children(":first").attr('type') === 'radio') {
                                                    var radioName = ($('#' + display).children(":first").attr('name'))
                                                    $('input:radio[name="' + radioName + '"]').change(function () {
                                                        if ($('#' + display).children(":first").is(":checked")) {
                                                            var value = $('.field.' + name).attr("value");
                                                            showUp(value)
                                                        } else {
                                                            $('.field.' + name).hide();
                                                        }
                                                    });
                                                }
                                            }
                                            // Check box dependent
                                            if ($('#' + display).is("div") === true) {
                                                $('#' + display).change(function () {
                                                    if ($(this).children(":first").attr('type') === 'checkbox') {
                                                        if ($(this).children(":first").is(":checked")) {
                                                            if ($('.field.' + name).attr("name") === "Select") {
                                                                $('.field.' + name).attr("name", 'CheckboxSelect');
                                                            }
                                                            if ($('.field.' + name).attr("name") !== "CheckboxSelect") {
                                                                $('.field.' + name).attr("name", 'Checkbox');
                                                            }
                                                            var value = $('.field.' + name).attr("value");
                                                            showUp(value)
                                                        } else {
                                                            if ($('.field.' + name).attr("name") !== "Select" &&
                                                                $('.field.' + name).attr("name") !== "CheckboxSelect" &&
                                                                $('.field.' + name).attr("name") !== "CheckboxCheckbox") {
                                                                $('.field.' + name).attr("name", 0);
                                                            }
                                                            var value = $('.field.' + name).attr("value");
                                                            notShow(value)
                                                        }
                                                    }
                                                });
                                            }
                                            // Select box dependent
                                            if ($('#' + display).is("option") === true) {
                                                triggerDependents.push("#" + $('#' + display).parent().attr("id"));
                                                $('#' + display).parent().change(function () {
                                                    var id = $('#' + display).parent().attr('id')
                                                    $('#' + id).find('option').each(function () {
                                                        if ($('#' + display).is(':selected')) {
                                                            if ($('.field.' + name).attr("name") === "Checkbox") {
                                                                $('.field.' + name).attr("name", 'SelectCheckbox');
                                                            }
                                                            if ($('.field.' + name).attr("name") !== "SelectCheckbox") {
                                                                $('.field.' + name).attr("name", 'Select');
                                                            }
                                                            var value = $('.field.' + name).attr("value");
                                                            showUp(value)
                                                        } else {
                                                            if ($('.field.' + name).attr("name") !== "Checkbox" &&
                                                                $('.field.' + name).attr("name") !== "SelectCheckbox") {
                                                                $('.field.' + name).attr("name", 0);
                                                            }
                                                            var value = $('.field.' + name).attr("value");
                                                            notShow(value)
                                                        }
                                                    })
                                                });
                                            }
                                        }
                                    });
                                });
                            });
                        });
                    }
                });
            });
            $.each(triggerDependents, function(index, triggerDependent) {
                $(triggerDependent).trigger("change");
            })
        });
    };
});
