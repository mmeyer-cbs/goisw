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
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Bss_CompanyAccount/js/multi-wishlist/bss-wishlist'
], function ($, mAlert, $t) {
    "use strict";

    var compatibleMultiWishList = {
        _showPopup: function ($this) {
            var url = this.options.url_popup,
                that = this;
            $.ajax({
                url: url,
                async: false
            }).done(function (response) {
                if (response.cant_access) {
                    that.hidePopup();
                    $.bssfancybox.hideLoading();
                    $.bssfancybox.helpers.overlay.close();
                    mAlert({
                        title: $t('Opps...!'),
                        content: response.error_message
                    });
                } else {
                    return that._super($this);
                }
            }).fail(function () {
                alert('Your account can not get access this action.');
            });
            return false;
        },
    };

    return function (targetWidget) {
        $.widget('mage.MultiWishlist', targetWidget, compatibleMultiWishList); // the widget alias should be like for the target widget

        return $.mage.MultiWishlist; //  the widget by parent alias should be returned
    };
});
