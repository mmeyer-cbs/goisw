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
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    "use strict";
    $(document).on('click','.action.toquote.primary',function(){
        let form = $(this).closest("form");
        let addToQuote = changeCartUrl($(form).prop('action'));
        if (!addToQuote){
            addToQuote = $(form).prop('action');
        }
        let formData = new FormData(form[0]);
        if(checkActionClickAddToQuote(this, formData.get('product')))
        {
            formData.set('quoteextension', '1');
            formData.set('ajax', '1');
            $.ajax({
                url: addToQuote,
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function(res) {
                    let size = $(".swatch-option.text.selected");
                    let color = $(".swatch-option.color.selected");
                    if (size.length !== 0){
                        for (let i = 0; i < size.length; i++){
                            let className = size[i].className;
                            className = className.replace("swatch-option text selected","swatch-option text");
                            size[i].className = className;
                        }
                    }
                    if (color.length !== 0){
                        for (let i = 0; i < color.length; i++) {
                            let className = color[i].className;
                            className = className.replace("swatch-option color selected", "swatch-option color");
                            color[i].className = className;
                        }
                    }
                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.messages) {
                        $('.page.messages')[0].childNodes[0].dataset.placeholder = res.messages;
                    }
                },
                fail: function(xhr, textStatus, errorThrown){
                    alert(errorThrown);
                }
            });
        }
    });

    /**
     * Check in catalog page is true, in product detail and wishlist not true
     *
     * @param {Object} ele
     * @param {string} productId
     */
    function checkActionClickAddToQuote(ele, productId) {
        let currentClassName = ele.className;

        let className = 'action toquote primary product-';
        className+= productId;
        return currentClassName === className;
    }

    /**
     * Change cart url to quote url
     *
     * @param {string} cartUrl
     */
    function changeCartUrl(cartUrl) {
        let routerQuoteUrl = "quoteextension/quote/add";
        let newCartUrl = cartUrl.substring(BASE_URL.length);
        let routerCartUrl = newCartUrl;
        if (newCartUrl.split("/").length < 3) {
            return '';
        }
        if (newCartUrl.split("/").length > 4) {
            let routerCartUrl = newCartUrl.substr(
                0,
                newCartUrl.indexOf("/", newCartUrl.indexOf("/", newCartUrl.indexOf("/") + 1) + 1)
            );
        }
        return cartUrl.replace(routerCartUrl, routerQuoteUrl);
    }

    return function (config, element) {
        if ($(element).parents('.products-related').length || $(element).parents('.products-upsell').length) {
            $(element).remove();
        }
        addQuoteButton('.action.tocart', $(element), 0);

        addToWishlistItemId('.action.toquote', $(element));

        $(".input-text.qty").change(function(){
            var buttonQuote = $(this).parents(".product-item").find(".action.toquote.primary")
            var dataPost = buttonQuote.attr('data-post');
            dataPost = JSON.parse(dataPost);
            dataPost.data.qty = $(this).val();
            buttonQuote.attr('data-post', JSON.stringify(dataPost));
        });

        function addQuoteButton(sel, el, count) {
            if(el.parent().find(sel).length > 0) {
                el.parent().find(sel).parent().append(element);
            }else if (el.parent().find('.hide_price_text').length > 0){
                el.parent().find('.hide_price_text').parent().append(element);
            } else {
                count++;
                if(count < 3) {
                    el = el.parent();
                    addQuoteButton(sel, el, count);
                }
            }
        }

        function addToWishlistItemId (sel, el) {
            if(el.find(sel).length > 0) {
                let wishlist_item_id = el.parent().find('.action.tocart').attr('data-item-id');
                let postData =  el.find(sel).attr('data-post');
                if (postData){
                    let data = JSON.parse(postData);
                    data.data.item_id = wishlist_item_id;
                    data.data.qty = $(el).parents(".product-item").find(".input-text.qty").val();
                    el.find(sel).attr('data-post', JSON.stringify(data));
                }
            }
        }
        $(element).show();
    }
});
