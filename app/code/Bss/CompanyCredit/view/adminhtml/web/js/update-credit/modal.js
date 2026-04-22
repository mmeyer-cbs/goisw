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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/modal/modal-component',
    'Bss_CompanyCredit/js/grid/massaction/selections-update-credit'
], function (_, utils, Modal, selectionsUpdateCredit) {
    'use strict';

    return Modal.extend({
        defaults: {
            actionSelections: null,
            modules: {
                creditLimit: '${ $.creditLimit }',
                paymentDueDate: '${ $.paymentDueDate }',
                updateAvailable: '${ $.updateAvailable }',
                comment: '${ $.comment }',
                allowExceed: '${ $.allowExceed }',
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super().observe('actionSelections');

            return this;
        },

        /**
         * Open modal window.
         *
         * @param {Component} component - Magento UI Component.
         * @param {Object} data - Company listing table options.
         */
        openModal: function (component, data) {
            var selections = selectionsUpdateCredit.updateCredit(data);

            this.actionSelections(selections);
            this._super();
        },

        /**
         * Close modal window.
         */
        closeModal: function () {
            this._super();
        },

        /**
         * Mass update converting of credit.
         */
        updateCredit: function () {
            var data;
            var self = this;
            var listUi = [
                this.creditLimit(),
                this.paymentDueDate(),
                this.updateAvailable(),
                this.comment(),
                this.allowExceed()
            ];

            this.valid = true;
            listUi.forEach(function checkValidData(item) {
                self.validate(item);
            })

            if (this.valid) {
                data = {
                    'credit_limit': this.creditLimit().value(),
                    'payment_due_date': this.paymentDueDate().value(),
                    'update_available': this.updateAvailable().value(),
                    'comment': this.comment().value(),
                    'allow_exceed': this.allowExceed().value(),
                };

                if (data.credit_limit >= 0 &&
                    (Number(data.credit_limit) || Number(data.credit_limit) === 0) ||
                    Number(data.update_available) && data.update_available !== "0" ||
                    data.allow_exceed
                ) {
                    data = _.extend(data, this.actionSelections());

                    utils.submit({
                        url: this.massUpdateCreditUrl,
                        data: data
                    });

                    this.closeModal();
                }

            }
        }

    });
});
