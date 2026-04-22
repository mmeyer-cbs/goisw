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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'Magento_Ui/js/form/components/insert-form',
    'Bss_CompanyAccount/js/bss_notification'
], function (Insert, bssNotification) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                roleListing: '${ $.roleListingProvider }',
                roleModal: '${ $.roleModalProvider }'
            }
        },

        /**
         * Close modal, reload customer address listing and save customer address
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (!responseData.error) {
                this.roleModal().closeModal();
                this.roleListing().reload({
                    refresh: true
                });
            }
            bssNotification.bssNotification(responseData);
        },

        /**
         * Event method that closes "Edit role" modal and refreshes grid after role
         * was removed through "Delete" button on the "Edit role" modal
         *
         * @param {String} id - role ID to delete
         */
        onRoleDelete: function (id) {
            this.roleModal().closeModal();
            this.roleListing().reload({
                refresh: true
            });
        }
    });
});
