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
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate',
    'Bss_CustomPricing/js/bss_notification',
    'uiRegistry'
], function (Massactions, uiAlert, _, $, $t, bssNotification, registry) {
    'use strict';

    return Massactions.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                massaction: 'onAction'
            }
        },

        /**
         * Reload data listing
         *
         * @param {Object} data
         */
        onAction: function (data) {
            if (data.action === 'delete') {
                this.source.reload({
                    refresh: true
                });
            }
        },

        /**
         * Get rule id by route path
         *
         * @returns {string|null}
         */
        getRuleId: function () {
            var fullPath = window.location.pathname,
                /* matched "['/id/123', '123']" */
                fullMatch = fullPath.match(/(?:\/id\/)(\d*)/);
            if (fullMatch) {
                return fullMatch[1];
            }
            return false;
        },

        /** @inheritdoc */
        _getCallback: function (action, selections) {
            if (action.type === "multiple_update_form") {
                var itemsType = selections.excludeMode ? 'excluded' : 'selected',
                data = {};

                data[itemsType] = selections[itemsType];

                if (!data[itemsType].length) {
                    data[itemsType] = false;
                }

                _.extend(data, selections.params || {});

                var ruleId = this.getRuleId(),
                    callbacks = [
                        {
                            provider: 'bss_price_rule_form.areas.product_price.product_price.multiple_update_custom_price_modal.edit_multiple_update_custom_price_loader',
                            target: 'destroyInserted'
                        },
                        action.callback,
                        {
                            provider: 'bss_price_rule_form.areas.product_price.product_price.multiple_update_custom_price_modal.edit_multiple_update_custom_price_loader',
                            target: 'render',
                            bss_params: {
                                rule_id: ruleId,
                                data: data,
                                make_post_request: true
                            }
                        }
                    ],
                    args = [];
                return function () {
                    _.each(callbacks, function (cb) {
                        args = [action, selections];
                        if (cb.bss_params) {
                            args.unshift(cb.bss_params);
                        }
                        args.unshift(cb.target);
                        var callback = registry.async(cb.provider);
                        callback.apply(null, args);
                    });
                }
            } else {
                return this._super(action, selections);
            }
        },

        /**
         * Default action callback. Send selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType, selections,
                ruleId = this.getRuleId();
            if (ruleId && action.isAjax) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                selections = {};

                selections[itemsType] = data[itemsType];

                if (!selections[itemsType].length) {
                    selections[itemsType] = false;
                }

                _.extend(selections, data.params || {});

                this.request(action.url, {
                    rule_id: ruleId,
                    data: selections
                }).done(function (response) {
                    if (!response.error) {
                        this.trigger('massaction', {
                            action: action.type,
                            data: selections
                        });
                    }
                }.bind(this));
            } else {
                this._super();
            }
        },

        /**
         * Send listing data mass action ajax request
         *
         * @param {String} href
         * @param {Object} data
         */
        request: function (href, data) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: data
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    }
                    bssNotification.bssNotification(response, 10000);
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
