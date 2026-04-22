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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    "use strict";
    $.widget('bss.customAddressAttribute', {
        options: {
            customAddress: ''
        },
        _create: function () {
            let customerAddresss = this.options.customAddress;
            var objectSelectFile = [];
            var $selectFile = '';
            $('.form-inline div.admin__field').each(function () {
                let id  = $(this).find('input').attr('id');
                let idTextArea = $(this).find('textarea').attr('id');
                let idSelectType = $(this).find('select').attr('id');
                Object.keys(customerAddresss).forEach(function (key) {
                    if (key === id) {
                        if ($('#'+id).attr('type') === 'file') {
                            $selectFile = $('#'+id);
                            objectSelectFile.push(id);
                            $('#'+id+'_value').val(customerAddresss[key]['valueOld']);
                            var displayFile = '<span id="display_file_' + id + '">' + customerAddresss[key]['value'] + "</span>"
                            $selectFile.parent().append(displayFile);
                        } else {
                            $('input#'+id).val(customerAddresss[key]['value']);
                        }
                    } else if (key === idTextArea) {
                        $('textarea#'+idTextArea).html(customerAddresss[key]['value']);
                    } else if (key === idSelectType) {
                        $('#'+idSelectType + ' > option').each(function () {
                            let optionArray = customerAddresss[key]['value'].split(",");
                            if (optionArray.length > 0) {
                                if (optionArray.includes(this.text)) {
                                    this.setAttribute('selected','selected');
                                }
                            }
                            if (this.text === customerAddresss[key]['value']) {
                                this.setAttribute('selected','selected');
                            }
                        });
                    }
                })
            })

            if (objectSelectFile) {
                $.each(objectSelectFile, function (index, id) {
                    $('#'+id).click(function () {
                        $('#'+id+'_value').val("");
                        $('#display_file_'+id).html("");
                    })
                });
            }
        },
    });

    return $.bss.customAddressAttribute;
});
