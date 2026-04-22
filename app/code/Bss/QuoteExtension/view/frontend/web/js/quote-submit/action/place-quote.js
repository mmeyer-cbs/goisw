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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/place-order',
        'Bss_QuoteExtension/js/quote-submit/action/redirect-on-success',
        'Bss_QuoteExtension/js/quote-submit/model/resource-url-manager',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Bss_QuoteExtension/js/full-screen-loader',
        'Magento_Customer/js/model/customer',
        'mage/url'
    ],
    function (
        $,
        quote,
        placeOrderService,
        redirectOnSuccessAction,
        resourceUrlManagerModel,
        $t,
        globalMessageList,
        fullScreenLoader,
        customer,
        url
    ) {
        'use strict';

        /**
         * This action handles the quotation placement (saves the quote)
         */
        return function (shippingSameAsBilling) {

            /**
             * Handle undefined result
             *
             * @return void
             */
            function handleUndefined()
            {
                globalMessageList.addErrorMessage({
                    message: $t('Something went wrong while processing your quote. Please try again later.')
                });

                stopLoader();
                scrollToTop();
            }

            /**
             * Handle when success
             *
             * @return void
             */
            function handleSuccess()
            {
                redirectOnSuccessAction.execute();
            }

            /**
             * Handle when error
             *
             * @return void
             * @param result
             */
            function handleError(result)
            {
                if (result.status == 401) {
                    window.location.replace(url.build('customer/account/login/'));
                } else {
                    if (result.error != 'undefined') {
                        $.each(result.error.split('/n'), function (index, errorMessage) {
                            globalMessageList.addErrorMessage({message: errorMessage});
                        });
                    }

                    stopLoader();
                    scrollToTop();
                }
            }

            /**
             * Two times stop loader because placeOrderService creates an extra loader.
             *
             * @return void
             */
            function stopLoader()
            {
                fullScreenLoader.stopLoader(true);
                fullScreenLoader.stopLoader(true);
            }

            /**
             * Scroll the page to the error
             * @reutn void
             */
            function scrollToTop()
            {
                $('html, body').animate({scrollTop: $("#quoteSubmit").offset().top}, 500);
            }

            /**
             * Get an object of quote data
             *
             * @return object
             */
            function getQuoteData()
            {
                var shippingMethod = [];
                if (quote.shippingMethod()) {
                    shippingMethod = quote.shippingMethod();
                }
                var shippingAddress = [];
                if (quote.shippingAddress()) {
                    shippingAddress = quote.shippingAddress();
                }

                return {
                    cart_id: quote.getQuoteId(),
                    customer_note: $("textarea[name=customer_note]").val(),
                    shippingMethod: shippingMethod,
                    shippingAddress: shippingAddress,
                    additional_data: {
                        'email': $("input[name=username]").val(),
                        'customer_firstname': $("input[name=personal-information-firstname]").val(),
                        'customer_lastname': $("input[name=personal-information-lastname]").val(),
                    }
                };
            }

            /**
             * Get the request URL
             *
             * @returns {*|string}
             */
            function getRequestUrl()
            {
                return resourceUrlManagerModel.getUrlForPlaceQuote(quote, getQuoteData());
            }

            /**
             * Place the quote
             *
             * @see QuoteExtension/Model/PlaceQuote.php
             */
            return placeOrderService(getRequestUrl(), getQuoteData(), globalMessageList)

            .done(
                function (result) {
                    if (result) {
                        handleSuccess();
                    } else {
                        handleUndefined();
                    }
                }
            ).fail(
                function (response) {
                    if (response.status == 404 || response.status == 403) {
                        location.reload();
                    }
                    stopLoader();
                    scrollToTop();
                }
            );
        };
    }
);
