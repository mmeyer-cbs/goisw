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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Magento_Ui/js/form/components/insert-form',
    'Bss_CustomPricing/js/bss_notification',
    'underscore',
    'mageUtils'
], function ($, Insert, bssNotification, _, utils) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                productPriceListing: '${ $.productPriceListingProvider }',
                productPriceModal: '${ $.productPriceModalProvider }'
            }
        },

        /**
         * Close modal, reload sub-user listing
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (!responseData.error) {
                this.productPriceModal().closeModal();
                this.productPriceListing().reload({
                    refresh: true
                });
                bssNotification.bssNotification(responseData);
            }
        },

        /**
         * Change the ajax method to POST to avoid long params URI
         *
         * @param {Object} params
         * @param {Array} ajaxSettings
         * @returns {*}
         */
        requestData: function (params, ajaxSettings) {
            if (params.make_post_request) {
                delete params.make_post_request;
                var query = utils.copy(params);

                ajaxSettings = _.extend({
                    url: this['update_url'],
                    method: 'POST',
                    data: query,
                    dataType: 'json'
                }, ajaxSettings);

                this.loading(true);

                return $.ajax(ajaxSettings);
            }

            return this._super(params, ajaxSettings);
        }
    });
});

