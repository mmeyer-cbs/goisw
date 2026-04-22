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
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'mage/translate',
        'uiRegistry'
    ],
    function ($, ko, Component, $t, registry) {
        'use strict';

        var errorHtml = "<div role='alert' class='field-error'><span>" + $t('This is a required field.') + "</span></div>";

        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                $(document.body).on("click", '#shipping-method-buttons-container .continue', function (event, ui) {
                    return self.bssValidateField();
                });
            },

            bssValidateField: function () {
                var flag = true;
                var requireField = window.checkoutConfig.bssCA.requireField;

                $.each(requireField, function (index, value) {
                    if ($(value).length) {
                        if ($(value).attr('type') == 'checkbox' || $(value).attr('type') == 'radio') {
                            var countUnChecked = 0,
                                countInput = 0;
                            $(value).each(function () {
                                countInput++;
                                if (!$(this).is(':checked')) {
                                    countUnChecked++;
                                }
                            })
                            if (countUnChecked == countInput) {
                                flag = false;
                            }
                            if (flag == false) {
                                if (!$(value + ':last').parent().find('.field-error').length) {
                                    $(value + ':last').parent().append(errorHtml);
                                }
                            } else {
                                $(value + ':last').parent().find('.field-error').remove();
                            }
                        } else if ($(value).attr('type') == 'file') {
                            var name = $(value).attr('name');
                            var name = name.replace('bss_customer_attributes[', '');
                            var name = name.replace(']', '');
                            if (!$.isEmptyObject(window.checkoutConfig.customerData.bssCheckout)
                                && !$.isEmptyObject(window.checkoutConfig.customerData.bssCheckout[name])) {
                                return $(value).parent().find('.field-error').remove();
                            }
                            if (!$(value).find('.field-error').length) {
                                $(value).parent().append(errorHtml);
                            }
                            flag = false;
                        } else {
                            if ($(value).val() == null || $(value).val() == '') {
                                if (!$(value).parent().find('.field-error').length) {
                                    $(value).parent().append(errorHtml);
                                }
                                flag = false;
                            } else {
                                $(value).parent().find('.field-error').remove();
                            }
                        }
                    }
                });

                if (flag) {
                    let source = registry.get('checkoutProvider');
                    if (source.get('shippingAddressLogin') || $('[name^=shippingAddressLogin]').length > 0) {
                        source.set('params.invalid', false);
                        source.trigger('shippingAddressLogin.data.validate');
                        if (source.get('params.invalid')) {
                            return false;
                        }
                    }
                }
                return flag;
            }
        });
    }
);
