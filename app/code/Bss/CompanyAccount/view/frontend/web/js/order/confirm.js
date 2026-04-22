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
    "jquery",
    "Magento_Ui/js/modal/modal",
    "mage/url"
], function ($, modal, urlBuilder) {
    return function (config) {
        var id = $('.bss-action-order').attr('data-th');
        var approveOptions = popup('approve', id);
        var rejectOptions = popup('reject', id);
        var rejectPopup = modal(rejectOptions, $('#confirm-reject'));
        var approvePopup = modal(approveOptions, $('#confirm-approve'));

        $(document).ready(function () {
            $(".bss-btn-action.reject").on('click', function () {
                $("#confirm-reject").modal(rejectPopup).modal("openModal");
                $("#confirm-reject").find('.modal-inner-content h2').text('Order ID #'+id);
            });
            $(".bss-btn-action.approve").on('click', function () {
                $("#confirm-approve").modal(approvePopup).modal("openModal");
                $("#confirm-approve").find('.modal-inner-content h2').text('Order ID #'+id);
            });
            $(".bss-btn-checkout").on('click',function () {
                if (window.urlCheckout) {
                    window.location.href = window.urlCheckout;
                } else {
                    let params = {'order_id' : id};
                    window.location.href = urlBuilder.build("companyaccount/order/checkout?" + jQuery.param(params));
                }
            })
        });

        function popup(action, id)
        {
            let upperTitle;
            if (action === 'approve') {
                upperTitle = 'APPROVE';
            } else {
                upperTitle = 'REJECT';
            }
            return {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'CONFIRM ' + upperTitle,
                modalClass: 'bss-confirm',
                buttons: [{
                    text: $.mage.__('Continue'),
                    class: '', click: function () {
                        var params = {'action': action, 'order_id': id};
                        window.location.href = urlBuilder.build("companyaccount/order/approve?" + jQuery.param(params));
                    }
                }, {
                    text: $.mage.__('Cancel'),
                    class: '', click: function () {
                        this.closeModal();
                    }
                },]
            };
        }
    }
})
