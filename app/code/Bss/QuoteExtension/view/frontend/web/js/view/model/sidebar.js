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

define([], function () {
    'use strict';

    return {
        popUp: false,

        /**
         * @param {Object} popUp
         */
        setPopup: function (popUp) {
            this.popUp = popUp;
        },

        /**
         * Show popup.
         */
        show: function () {
            if (this.popUp) {
                this.popUp.modal('openModal');
            }
        },

        /**
         * Hide popup.
         */
        hide: function () {
            if (this.popUp) {
                this.popUp.modal('closeModal');
            }
        }
    };
});
