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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Bss_StoreCredit/js/model/payment/storecredit-messages',
        'mage/storage',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (ko, $, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction, totals, fullScreenLoader) {
        'use strict';

        return function (amount, isApplied, totalAmountCustomer) {
            var urls = {
                        'guest': '',
                        'customer': '/carts/mine/bss-store-credit/apply/' + amount()
                },
                url = urlManager.getUrl(urls, {}),
                message = '';

            fullScreenLoader.startLoader();

            return storage.put(
                url,
                {},
                false
            ).done(
                function (response) {
                    var res = JSON.parse(response);
                    if (res.status) {
                        var deferred = $.Deferred();
                        message = res.message;
                        isApplied(true);
                        totals.isLoading(true);
                        getPaymentInformationAction(deferred);
                        $.when(deferred).done(function () {
                            fullScreenLoader.stopLoader();
                            totals.isLoading(false);
                        });
                        if (parseFloat(res.amount)) {
                            amount(parseFloat(res.amount));
                        } else {
                            isApplied(false);
                            $('#bss-store-credit-code').val('');
                        }
                        if (res.total) {
                            totalAmountCustomer(res.total);
                        }
                        if (res.notice) {
                            message += res.notice;
                        }
                        messageContainer.addSuccessMessage({
                            'message': message
                        });
                    } else {
                        message = res.message;
                        fullScreenLoader.stopLoader();
                        messageContainer.addErrorMessage({
                            'message': message
                        });
                    }
                }
            ).fail(
                function (response) {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);
