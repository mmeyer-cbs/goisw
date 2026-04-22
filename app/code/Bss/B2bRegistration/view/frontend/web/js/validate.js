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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
require([
    'jquery',
    'mage/mage'
], function ($) {

    var dataForm = $('#form-validate');
    
    dataForm.mage('validation', {
        errorPlacement: function (error, element) {
            if (element.prop('id').search('full') !== -1) {
                var dobElement = $(element).parents('.customer-dob'),
                    errorClass = error.prop('class');
                error.insertAfter(element.parent());
                dobElement.find('.validate-custom').addClass(errorClass)
                    .after('<div class="' + errorClass + '"></div>');
            } else {
                error.insertAfter(element);
            }
        },
    }).find('input:text').attr('autocomplete', 'off');

});
