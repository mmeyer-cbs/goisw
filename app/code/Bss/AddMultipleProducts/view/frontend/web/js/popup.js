define([
    'jquery',
    'mage/mage',
    'bssowlslider'
], function ($) {
    'use strict';
    $.widget('mage.popupMultipleaddtocart', {
        options: {
            items: '',
            slideSpeed: '',
            autoPlay: '',
            countDown: '',
            addedProductCount: 0
        },
        _create: function () {
            var $widget = this;
            var count;
            var dataForm = $('#product_addmuntile_form_popup');
            dataForm.mage('validation', {});
            $widget._createSlide();

            $(document).on('click', '.btn-continue,.fancybox-close', function () {
                $.fancybox.close();
                clearInterval(count);
            });

            $('.fancybox-close,.fancybox-overlay,.addmanytocart-popup').on('click', function () {
                clearInterval(count);
            });
            var countDown = this.options.countDown;
            count = setInterval(function () {
                countDown -= 1;
                $('span.countdown').text("(" + countDown + ")");
                if (countDown == 0) {
                    $('span.countdown').parent().trigger("click");
                    clearInterval(count);
                }
            }, 1000);


            $('.remove-er-pu').on('click', function () {
                $(this).parents('.item-er-pu').remove();
                if ($('.item-er-pu').length == 0) {
                    $('#product-addtocart-button-er-pu').remove();
                }
            })

            $('.info-er-pu').each(function () {
                $(this).find('.price-to .price-label').remove();
                $(this).find('.price-from').remove();
            })

            $('.option-er-pu').each(function () {
                var $productid = $(this).find('input[name="productid"]').val();
                $(this).find('input').each(function () {
                    $(this).attr('name', $productid + '_' + $(this).attr('name'));
                })
                $(this).find('textarea').each(function () {
                    $(this).attr('name', $productid + '_' + $(this).attr('name'));
                })
                $(this).find('select').each(function () {
                    $(this).attr('name', $productid + '_' + $(this).attr('name'));
                })
            })

            $('.field.date').each(function () {
                var selectfirst = $(this).find('select').get(0);
                $(selectfirst).find('option').attr('price', $(this).find('input[type="hidden"]').attr('price'));
            })

            $('.info-er-pu .price-box').each(function () {
                $(this).parent().find('.fixed-price-ad-pu span.finalPrice').text($(this).find('span[data-price-type="finalPrice"]').text());
                $(this).parent().find('.fixed-price-ad-pu span.basePrice').text($(this).find('span[data-price-type="basePrice"] > .price').text());
                $(this).parent().find('.fixed-price-ad-pu span.oldPrice').text($(this).find('span[data-price-type="oldPrice"] > .price').text());
            })


        },

        _createSlide: function () {
            var $widget = this,
                owlItemDisplay = $widget.options.items,
                owl = $('#product-slider'),
                owlItemMargin = 5;

            if ($widget.options.addedProductCount <= owlItemDisplay) {
                owlItemDisplay = $widget.options.addedProductCount;
            }

             if (owlItemDisplay < 5) {
                 owlItemMargin = 10;
             }

            owl.owlCarousel({
                loop: true,
                margin: owlItemMargin,
                stagePadding: 0,
                nav: true,
                navText: [
                    `
                        <div id="arrow-container">
                            <span class="arrow primera prev"></span>
                            <span class="arrow segunda prev"></span>
                        </div>
                    `,
                    `
                        <div id="arrow-container">
                            <span class="arrow primera next"></span>
                            <span class="arrow segunda next"></span>
                        </div>
                    `
                ],
                navElement: 'div type="button" role="presentation"',
                autoplay: $widget.options.autoPlay,
                autoplayHoverPause: true,
                autoplaySpeed: $widget.options.slideSpeed,
                dots: false,
                dotsSpeed: 800,
                items: owlItemDisplay,
                responsive: {
                    320: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    1199: {
                        items: owlItemDisplay
                    }
                }
            });
        }
    });

    return $.mage.popupMultipleaddtocart;
});
