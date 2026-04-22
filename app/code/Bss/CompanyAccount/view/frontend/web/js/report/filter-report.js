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
    "mage/url"
], function ($, urlBuilder) {
    $(document).ready(function () {
        $('#bss-action-filter').click(function (event) {
            ajaxReloadFilter($('#date-from').val(), $('#date-to').val(), event);
        });
        $('#submit-filter').click(function () {
            $("#bss-filter-nav").toggle();
        });

        $('#bss-cancel-filter').click(function (e) {
            $('#bss-filter-nav').hide();
            $('#date-from').val('');
            $('#date-to').val('');
            ajaxReloadFilter('', '', e);
        })

        $('#export-btn').click(function () {
            var params = {'datefrom': $('#date-from').val(), 'dateto': $('#date-to').val()};
            window.location.href = urlBuilder.build('companyaccount/report/export?' + jQuery.param(params));
        })
    });

    function ajaxReloadFilter(dateFrom, dateTo, event)
    {
        event.preventDefault();
        let url = urlBuilder.build('companyaccount/report/filter');
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            showLoader: true,
            data: {
                datefrom: dateFrom,
                dateto: dateTo,
            },
            success: function (response) {
                $('#bss-table-filter').html(response.output);
            },
            error: function () {
                console.log('Error happens. Try again.');
            }
        });
    }
})
