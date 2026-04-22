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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(["jquery"], function ($) {
    $(document).ready(function () {
        var documentPadding = 50;
        var firstAttempt = true;
        var lastHeight = 0, curHeight = 0;
        var parentBody = window.parent.document.body;
        var ignoreUrl = ['#additional', '#reviews', '#description'];
        $('.mfp-preloader', parentBody).css('display', 'none');
        $('.mfp-iframe-holder .mfp-content', parentBody).css('width', '100%');

        $('.mfp-iframe-scaler iframe', parentBody).animate({'opacity': 1}, 2000);
        $('.reviews-actions a').attr('target', '_parent');
        $('.product-social-links a').attr('target', '_parent');
        $('body').css('overflow', 'hidden');

        setInterval(function () {
            if (firstAttempt) {
                curHeight =  $('.page-wrapper').outerHeight(true) + documentPadding;
            } else {
                curHeight =  $('.page-wrapper').outerHeight(true);
            }
            var documentHeight = curHeight + "px";
            if (curHeight != lastHeight) {
                $('.mfp-iframe-holder .mfp-content', parentBody).animate({
                    'height': documentHeight
                }, 500);
                lastHeight = curHeight;
                firstAttempt = false;
            }
        }, 500);

        // avoid opening more popup
        $(document).on('click','a', function(e){
            e.preventDefault();
            if (!$(this).attr('data-post')) {
                url = $(this).attr('href');
                if (!($.inArray(url, ignoreUrl) !== -1)) {
                    self.parent.location.href = url;
                    return false;
                }
            }
        })
    });
});