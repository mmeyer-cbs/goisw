define(
    [
        'jquery',
        'mage/storage',
        'Mageplaza_AjaxLayer/js/model/loader',
        'mage/apply/main',
        'ko',
    ],
    function ($, storage, loader, mage, ko) {
        'use strict';

        var productContainer   = $('#layer-product-list'),
            layerContainer     = $('.layered-filter-block-container'),
            quickViewContainer = $('#mpquickview-popup');

        return function (submitUrl, isChangeUrl, method) {
            /** save active state */
            var actives = [],
                data;
            $('.filter-options-item').each(function (index) {
                if ($(this).hasClass('active')) {
                    actives.push($(this).attr('attribute'));
                }
            });
            window.layerActiveTabs = actives;

            /** start loader */
            loader.startLoader();


            /** change browser url */
            if (typeof window.history.pushState === 'function' && (typeof isChangeUrl === 'undefined')) {
                window.history.pushState({url: submitUrl}, '', submitUrl);
            }

            if (submitUrl.includes('product_compare') && method === 'post') {// For 'add to wishlist' & 'add to compare' event
                return storage.post(submitUrl).done(
                ).fail(
                    function () {
                        window.location.reload();
                    }
                ).always(function () {
                    loader.stopLoader();
                });
            }

            if (!submitUrl.includes('multiwishlist') && !submitUrl.includes('undefined')) return storage.get(submitUrl).done(
                function (response) {
                    if (response.backUrl) {
                        window.location = response.backUrl;
                        return;
                    }
                    if (response.navigation) {
                        layerContainer.html(response.navigation);
                    }
                    if (response.products) {
                        productContainer.html(response.products);
                    }
                    if (response.quickview) {
                        quickViewContainer.html(response.quickview);
                    }

                    ko.cleanNode(productContainer[0]);
                    productContainer.applyBindings();

                    if (mage) {
                        $("html, body").animate({scrollTop: $('#layer-product-list').offset().top - 100}, "slow");
                        mage.apply();
                    }
                }
            ).fail(
                function () {
                    window.location.reload();
                }
            ).always(
                function () {
                    var colorAttr = $('.filter-options .filter-options-item .color .swatch-option-link-layered .swatch-option');

                    colorAttr.each(function(){
                        var el  = $(this),
                            hex = el.attr('data-option-tooltip-value');
                        if(typeof hex != "undefined"){
                            if (hex.charAt(0) === '#') {
                                hex = hex.substr(1);
                            }
                            if ((hex.length < 2) || (hex.length > 6)) {
                                el.attr('style','background: '+el.attr('data-option-label')+';');
                            }
                            var values = hex.split(''),
                                r,
                                g,
                                b;

                            if (hex.length === 2) {
                                r = parseInt(values[0].toString() + values[1].toString(), 16);
                                g = r;
                                b = r;
                            } else if (hex.length === 3) {
                                r = parseInt(values[0].toString() + values[0].toString(), 16);
                                g = parseInt(values[1].toString() + values[1].toString(), 16);
                                b = parseInt(values[2].toString() + values[2].toString(), 16);
                            } else if (hex.length === 6) {
                                r = parseInt(values[0].toString() + values[1].toString(), 16);
                                g = parseInt(values[2].toString() + values[3].toString(), 16);
                                b = parseInt(values[4].toString() + values[5].toString(), 16);
                            } else {
                                el.attr('style','background: '+el.attr('data-option-label')+';');
                            }

                            el.attr('style','background: center center no-repeat rgb('+[r, g, b]+');');
                        }

                    });

                    //selected

                    var filterCurrent = $('.layered-filter-block-container .filter-current .items .item .filter-value');

                    filterCurrent.each(function(){
                        var el         = $(this),
                            colorLabel = el.html(),
                            colorAttr  = $('.filter-options .filter-options-item .color .swatch-option-link-layered .swatch-option');

                        colorAttr.each(function(){
                            var elA = $(this);
                            if(elA.attr('data-option-label') === colorLabel && !elA.hasClass('selected')){
                                elA.addClass('selected');
                            }
                        });
                    });

                    loader.stopLoader();
                }
            );

            $.ajax({
                url: window.url_popup + 'action/add',
                success: function (response) {
                    if (response.url) {
                        window.location.href = response.url;
                    } else {
                        $.bssfancybox({
                            'content' : response,
                            helpers:  {
                                title : {
                                    type : 'inside'
                                },
                                overlay : {
                                    showEarly : true,
                                    locked : false
                                }
                            }
                        });
                        if ($('.wishlist-index-index').length) {
                            $('#create_wishlist').remove();
                        }
                    }
                    var wdth = $('.wishlist_btns').width();
                    $('.wishlist_btns button').css({'position':'relative','left': (wdth-$('.wishlist_btns button').outerWidth())/2 })
                },
                complete: function(data) {
                    var dataPost = $.parseJSON(window.datapost);

                    $('#add-to-multiwishlist .data-form').append('<input type="hidden" name="product" value="'+ dataPost.data.product+'">'+'<input type="hidden" name="bss_current_url" value="'+ window.bssCurrentUrl+'">'+
                        '<input type="hidden" name="uenc" value="'+ dataPost.data.uenc+'">');
                    loader.stopLoader();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loader.stopLoader();
                }
            });
        };
    }
);
