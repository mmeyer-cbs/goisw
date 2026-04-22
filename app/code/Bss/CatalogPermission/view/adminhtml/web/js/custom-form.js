/**
 * Bss Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   Bss
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 Bss Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    "domReady!"
], function ($, _) {
    'use strict';
    $.widget('bss.customformcms', {
        _init: function () {
            var self = this;
            self._RenderForm();
        },

        _RenderForm: function () {
            var self = this;
            $(document).on('change', 'select[name ="bss_redirect_type"]', function() {
                var redirectType = Number($(this).val());
                if (!$('select[name ="bss_select_page"]').length) {
                    return;
                }
                if (redirectType == 2) {
                    if ($('select[name ="bss_select_page"]').length) {
                        self._enabledElement($('select[name ="bss_select_page"]'));
                    }
                    if ($('input[name ="bss_custom_url"]').length) {
                        self._enabledElement($('input[name ="bss_custom_url"]'));
                    }
                    if ($('input[name ="bss_error_message"]').length) {
                        self._enabledElement($('input[name ="bss_error_message"]'));
                    }
                } else {
                    if ($('select[name ="bss_select_page"]').length) {
                        self._disabledElement($('select[name ="bss_select_page"]'));
                    }
                    if ($('input[name ="bss_custom_url"]').length) {
                        self._disabledElement($('input[name ="bss_custom_url"]'));
                    }
                    if ($('input[name ="bss_error_message"]').length) {
                        self._disabledElement($('input[name ="bss_error_message"]'));
                    }
                }
            });
        },

        _enabledElement: function ($element) {
            $element.prop("disabled", false);
            $element.parents('.admin__field').removeClass('_disabled');
        },

        _disabledElement: function ($element) {
            $element.prop("disabled", true);
            $element.parents('.admin__field').addClass('_disabled');
        }
    });
    return $.bss.customformcms;

});
