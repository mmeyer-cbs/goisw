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
define([
        'jquery'
    ],
    function ($) {
        'use strict';
        return function (Component) {
            return Component.extend
            (
                {
                    /**
                     * Handler of the file upload complete event.
                     * Save name file into window.checkoutConfig
                     *
                     * @param {Event} e
                     * @param {Object} data
                     */
                    onFileUploaded: function (e, data) {
                        var file = data.result,
                            error = file.error;
                        if (error) {
                            this.aggregateError("", error)
                        } else {
                            var bssCustomerAttribute = file.bss_customer_attributes;
                            if ($.isEmptyObject(window.checkoutConfig.customerData.bssCheckout)){
                                window.checkoutConfig.customerData.bssCheckout = [];
                            }
                            window.checkoutConfig.customerData.bssCheckout[bssCustomerAttribute] = file.filedValue;
                            this.addFile(file);
                            this.addFile(file);
                        }
                    },
                    /**
                     * Removes provided file from thes files list.
                     * Save name file into window.checkoutConfig
                     *
                     * @param {Object} file
                     * @returns {FileUploader} Chainable.
                     */
                    removeFile: function (file) {
                        window.checkoutConfig.customerData.bssCheckout[file.bss_customer_attributes] = "";
                        this.value.remove(file);
                        return this;
                    }
                }
            )
        }
    }
);
