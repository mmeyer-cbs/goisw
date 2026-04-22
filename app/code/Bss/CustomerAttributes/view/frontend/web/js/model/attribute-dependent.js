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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(['ko', 'uiRegistry', 'underscore'], function (ko, registry, _) {
    'use strict';

    return {
        data: ko.observableArray([]),
        workingElement: ko.observable(),

        /**
         * Handle the show/hide for attributes depended by the passed option
         *
         * @param {string} $inputOptionId - option is selected
         * @param {Object} attributeDpData -  attribute depend
         */
        processAttributeVisibility: function ($inputOptionId, attributeDpData) {
            // Loop attribute depend
            Object.keys(attributeDpData).forEach(($optionId) => {
                // @var attrData -  are attributes that are depended by $inputOptionId
                let attrData = attributeDpData[$optionId],
                    visible = true;
                // If option is selected === option in attribute depend data, then attributes dependent by that option will be visible = true
                // If not, vice versa
                if (Array.isArray($inputOptionId)) {
                    visible = $inputOptionId.includes($optionId) === true;
                } else {
                    visible = $inputOptionId === $optionId;
                }
                // Loop the attributes that depend on $inputOptionId is attributeDpData[$optionId]
                if (typeof attrData === 'object') {
                    attrData = Object.values(attrData);
                }
                attrData.forEach((attr) => {
                    if (typeof attr.dependent_attribute_value !== 'undefined') {
                        attr.dependent_attribute_value.forEach(element => {
                            element.inputOptionId = $optionId;
                            if (element.value === '1') {
                                element.label = 'Yes'
                            }
                            if (element.value === '0') {
                                element.label = 'No'
                            }
                        });
                    }
                    let activeParentCount = 1;
                    var duplicate = this.data().find(({value, dependent_attribute_value}) => value === attr.value);
                    var fake = {...attr};
                    if (duplicate !== undefined && duplicate.dependent_attribute_value !== undefined && Object.keys(duplicate.dependent_attribute_value).length === 0) {
                        if (visible === true) {
                            duplicate.dependent_attribute_value[$inputOptionId] = fake.dependent_attribute_value
                        } else {
                            delete duplicate.dependent_attribute_value[$optionId];
                        }
                        fake.activeParentCount = Object.keys(duplicate.dependent_attribute_value).length
                        if (Object.keys(duplicate.dependent_attribute_value).length > 0) {
                            Object.keys(duplicate.dependent_attribute_value).forEach(function (key) {
                                let keys = Object.keys(duplicate.dependent_attribute_value);
                                var ids = new Set(duplicate.dependent_attribute_value[key].map(d => d.value));
                                fake.dependent_attribute_value = [...duplicate.dependent_attribute_value[key], ...duplicate.dependent_attribute_value[keys[(keys.indexOf(key) + 1) % keys.length]].filter(d => !ids.has(d.value))]
                            });
                        }
                    } else {
                        if (visible === true) {
                            fake.activeParentCount = activeParentCount;
                            if (attr.dependent_attribute_value !== undefined) {
                                let options = [...attr.dependent_attribute_value];
                                fake.dependent_attribute_value = {};
                                fake.dependent_attribute_value[$inputOptionId] = options;
                            }
                            fake.inputOptionId = $optionId;
                            let faker = {...fake};
                            this.data.push(faker);
                        } else {
                            fake.inputOptionId = $optionId;
                        }
                        if (fake.dependent_attribute_value !== undefined) {
                            fake.dependent_attribute_value = attr.dependent_attribute_value;
                        }
                    }
                    this.workingElement(fake);
                });
            });
        },

    };
});
