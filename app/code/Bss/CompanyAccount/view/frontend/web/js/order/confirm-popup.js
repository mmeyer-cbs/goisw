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
    $(document).ready(function () {
        $(".action.order.reject").on('click', function () {
            var id = $(this).closest('td.col.actions').attr('data-th');
            var rejectOptions = popup('reject',id);
            var rejectPopup = modal(rejectOptions, $('#confirm-reject'));
            $("#confirm-reject").modal(rejectPopup).modal("openModal");
            $("#confirm-reject").find('.modal-inner-content h2').text('Order ID #'+id);

        });
        $(".action.order.approve").on('click', function () {
            var id = $(this).closest('td.col.actions').attr('data-th');
            var approveOptions = popup('approve',id);
            var approvePopup = modal(approveOptions, $('#confirm-approve'));
            $("#confirm-approve").modal(approvePopup).modal("openModal");
            $("#confirm-approve").find('.modal-inner-content h2').text('Order ID #'+id);
        });
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
            modalClass: 'bss-confirm',
            title: 'CONFIRM ' + upperTitle,
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
})
