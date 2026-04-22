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
define([
    'jquery'
], function ($) {
    "use strict";
    $.widget('bss.quoteVersionLog', {
        options: {
            logVersionSelector: '.version.quote',
            parentHistorySelector: '.history-log',
            itemLogTableSelector: '.item-log'
        },

        _create: function () {
            var self = this;
            $(self.options.logVersionSelector).on('click', function(event) {
                var elementTable = $(this).parents(self.options.parentHistorySelector).find(self.options.itemLogTableSelector);
                elementTable.fadeToggle("medium", "linear");
            });

        }
    });

    return $.bss.quoteVersionLog;
});