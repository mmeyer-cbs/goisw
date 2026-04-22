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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
        'mage/utils/wrapper',
        'jquery',
        'mage/url',
    ],
    function (wrapper, $, urlManager) {
        'use strict';

        return function (newCustomerAddressesFunction) {
            return wrapper.wrap(newCustomerAddressesFunction, function (newCustomerAddress, addressData) {

                var customAddressCurrent = addressData['custom_attributes'],
                    bssCustomAttributes = [],
                    dataCustomAddress = window.checkoutConfig.dataCustomAddress;

                try {
                    if (typeof customAddressCurrent === 'object' && customAddressCurrent !== null) {
                        $.each(customAddressCurrent, function (keyCurrent, itemCurrent) {
                                var attributeCode = itemCurrent["attribute_code"],
                                    lengthAttributeCode = attributeCode.length;
                                if (itemCurrent['value'] !== '') {
                                    if (dataCustomAddress[itemCurrent["attribute_code"]]) {
                                        if (typeof itemCurrent["value"] === "object" && itemCurrent["value"] !== null) {
                                            if (dataCustomAddress[itemCurrent["attribute_code"]]["type"] == "file" && itemCurrent["value"][0]) {
                                                itemCurrent["value"] = itemCurrent["value"][0].file;
                                                bssCustomAttributes.push(itemCurrent);
                                            } else {
                                                var labelMultiple = "",
                                                    value = "";
                                                $.each(dataCustomAddress[attributeCode], function (index, item) {
                                                    $.each(itemCurrent["value"], function (indexValue, itemValue) {
                                                        if (itemValue == item["value"]) {
                                                            if (labelMultiple) {
                                                                value += "," + item["value"];
                                                                labelMultiple += ", " + item["label"];
                                                            } else {
                                                                value = item["value"];
                                                                labelMultiple += item["label"];
                                                            }

                                                        }
                                                    });
                                                    if (itemCurrent["value"] === item["value"]) {
                                                        item.attribute_code = itemCurrent["attribute_code"];
                                                        bssCustomAttributes.push(item);
                                                    }

                                                })
                                                if (labelMultiple) {
                                                    itemCurrent.label = labelMultiple;
                                                    itemCurrent.value = value;
                                                    bssCustomAttributes.push(itemCurrent);
                                                    labelMultiple = "";
                                                }
                                            }

                                        } else {
                                            $.each(dataCustomAddress[itemCurrent["attribute_code"]], function (index, item) {
                                                if (itemCurrent["value"] === item["value"]) {
                                                    item.attribute_code = itemCurrent["attribute_code"];
                                                    bssCustomAttributes.push(item);
                                                }
                                            })
                                        }
                                    } else if (lengthAttributeCode == attributeCode.indexOf("[]-prepared-for-send") + 20) {
                                        try {
                                            attributeCode = attributeCode.slice(1, attributeCode.indexOf("[]-prepared-for-send") - 1);
                                            if (dataCustomAddress[attributeCode]) {
                                                var labelMultiple = "";
                                                $.each(dataCustomAddress[attributeCode], function (index, item) {

                                                    $.each(itemCurrent["value"], function (indexValue, itemValue) {
                                                        if (itemValue == item["value"]) {
                                                            if (labelMultiple) {
                                                                labelMultiple += ", " + item["label"];
                                                            } else {
                                                                labelMultiple += item["label"];
                                                            }

                                                        }
                                                    });
                                                    if (itemCurrent["value"] === item["value"]) {
                                                        item.attribute_code = itemCurrent["attribute_code"];
                                                        bssCustomAttributes.push(item);
                                                    }

                                                })
                                                if (labelMultiple) {
                                                    itemCurrent.label = labelMultiple;
                                                    itemCurrent.attribute_code = attributeCode;
                                                    bssCustomAttributes.push(itemCurrent);
                                                    labelMultiple = "";
                                                }
                                            }
                                        } catch (e) {
                                            console.log(e);
                                        }

                                    } else if (lengthAttributeCode != attributeCode.indexOf("[]") + 2) {
                                        bssCustomAttributes.push(itemCurrent);
                                    }
                                }
                            }
                        );
                    }
                    if (Object.keys(bssCustomAttributes).length) {
                        addressData.custom_attributes = bssCustomAttributes;
                        $.ajax({
                            url: urlManager.build('customerattribute/address/save'),
                            data: {'data': bssCustomAttributes},
                            dataType: 'json',
                            success: function (data) {
                            }
                        });
                    }
                } catch (e) {
                    console.log(e);
                }

                return newCustomerAddress(addressData);
            });
        };
    }
);
