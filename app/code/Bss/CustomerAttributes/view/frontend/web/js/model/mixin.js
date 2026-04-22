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
define(
    ['ko'],
    function (ko) {
        'use strict';
        return {
            data: ko.observableArray([]),
            onWorkingEleChanged: function (newVa) {
                if (newVa.value !== this.index) {
                    return false;
                }
                if (newVa.activeParentCount > 0) {
                    if (typeof this.data !== 'undefined' &&
                        this.data.activeParentCount > 0 &&
                        typeof this.data.dependent_attribute_value !== 'undefined' &&
                        typeof newVa.dependent_attribute_value !== 'undefined') {
                        this.data.dependent_attribute_value = this.data.dependent_attribute_value.concat(newVa.dependent_attribute_value).reduce((unique, o) => {
                            if (!unique.some(obj => obj.label === o.label && obj.value === o.value)) {
                                unique.push(o);
                            }
                            return unique;
                        }, []);
                    } else {
                        this.visible(true);
                        this.data = newVa;
                    }
                    if (typeof newVa.dependent_attribute_value !== 'undefined') {
                        if (this.data.dependent_attribute_value.length > newVa.dependent_attribute_value.length) {
                            this.options(this.data.dependent_attribute_value);
                        } else {
                            this.options(newVa.dependent_attribute_value);
                        }
                    }
                } else {
                    this.visible(false);
                    if (typeof this.data !== 'undefined' &&
                        this.data.inputOptionId === newVa.inputOptionId) {
                        this.data = []
                    }
                }
                //eslint-disable-next-line no-undef
                //Get visible value if duplicate which other
                if (typeof this.data !== 'undefined') {
                    if (typeof this.data.dependent_attribute_value !== 'undefined') {
                        var arr = (_.union(newVa.dependent_attribute_value, this.data.dependent_attribute_value));
                        if (typeof newVa.activeParentCount === 'undefined') {
                            arr.forEach((element, index) => {
                                if (element.inputOptionId === newVa.inputOptionId) {
                                    arr.splice(index)
                                }
                            });
                        }
                    }
                    const counts = _.countBy(arr, 'value')
                    var test = (_.filter(arr, x => counts[x.value] > 1, x => counts[x.label] > 1, x => counts[x.inputOptionId] > 1))
                    var result = test.reduce((unique, o) => {
                        if (!unique.some(obj => obj.label === o.label && obj.value === o.value && obj.inputOptionId === o.inputOptionId)) {
                            unique.push(o);
                        }
                        return unique;
                    }, []);
                    if (result.length === 0 && this.data.length !== 0
                        && JSON.stringify(this.data.dependent_attribute_value) !== JSON.stringify(newVa.dependent_attribute_value)
                    ) {
                        result = Object.values(this.data.dependent_attribute_value);
                    }
                }
                if (typeof newVa.activeParentCount === 'undefined') {
                    if (typeof this.data !== 'undefined' &&
                        typeof result !== 'undefined' &&
                        result.length > 0
                    ) {
                        result.forEach((element, index) => {
                            if (element.inputOptionId === newVa.inputOptionId) {
                                result.splice(index)
                            }
                        });
                        this.options(result);
                        this.visible(true);
                    } else {
                        if (typeof this.data !== 'undefined' &&
                            typeof this.data.dependent_attribute_value === 'undefined' &&
                            typeof newVa.dependent_attribute_value === 'undefined' &&
                            JSON.stringify(this.data.value) === JSON.stringify(newVa.value)
                        ) {
                            this.visible(true);
                        } else {
                            this.visible(false);
                        }
                    }
                    this.data = [];
                }
            },
        };
    }
);
