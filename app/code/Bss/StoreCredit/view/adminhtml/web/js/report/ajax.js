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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/alert",
    "Magento_Catalog/js/price-utils",
    "mage/calendar",
    "bss/storecredit_highcharts",
    "bss/storecredit_exporting"
], function ($, $t, alert, priceUtils) {
    "use strict";
    return function (config, element) {
        $('#bss_store_credit_report_from').calendar({
            buttonText: $t('Select Date'),
            dateFormat: 'MM/dd/y'
        })
        $('#bss_store_credit_report_to').calendar({
            buttonText: $t('Select Date'),
            dateFormat: 'MM/dd/y'
        })
        if (config.ajaxUrl) {
            sendAjax(config, element);
            $('#bss_store_credit_report_refresh').click(function (e) {
                e.preventDefault();
                sendAjax(config, element);
            });
        }
    };

    function sendAjax(config, element)
    {
        var from = $('#bss_store_credit_report_from').val(),
            to = $('#bss_store_credit_report_to').val();
        if (!from || !to || new Date(from).getTime() > new Date(to).getTime()) {
            alert({
                title: $t('Error'),
                content: $t('Please enter from < to')
            });
        }
        $.ajax({
            url: config.ajaxUrl,
            data: {
                from: from,
                to: to,
                dimension: $('#bss_store_credit_report_show_by').val(),
                website_id: $('#bss_store_credit_website').val(),
            },
            dataType: 'json',
            showLoader: true,
            success: function (result) {
                if (!result.status) {
                    alert({
                        title: $t('Error'),
                        content: $t('Please enter again')
                    });
                } else {
                    loadChart(JSON.parse(result.data));
                    $(element).trigger('contentUpdated');
                }
            },
            error: function () {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter again')
                });
            }
        });
    }

    function loadChart(data)
    {
        var currency = priceUtils.formatPrice(0, data.priceFormat);
        var price = "";
        if (currency.search("0.00") !== -1) {
            price = currency.replace('0.00', '{point.y:.2f}');
        } else {
            price = currency.replace('0', '{point.y:.2f}');
        }

        Highcharts.chart('bss_store_credit_report_content', {
            chart: {
                type: 'column'
            },
            colors: "#0A0 #F55347".split(" "),
            title: {
                text: $t('Store Credit Report')
            },
            xAxis: {
                categories: data.period,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: $t('Price')
                }
            },
            credits: {
                enabled: false
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>'+ price +'</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: data.amount
        });
    }
});
