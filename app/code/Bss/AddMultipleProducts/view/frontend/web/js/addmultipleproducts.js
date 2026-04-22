define(
    [
        'jquery',
        'underscore',
        "mage/template",
        'Magento_Ui/js/modal/alert',
        "mage/translate",
        'mage/mage',
        "bssfancybox",
        "domReady!"
    ], function ($, _, mageTemplate, mAlert) {
        'use strict';
        var elementCss;
        var displayAddMultipleQuote = 0;
        $.widget('mage.Multipleaddtocart', {
                options: {
                    jsonClassApply: {},
                    jsonDisplayAddMultipleCart: "",
                    jsonDisplayAddMultipleQuote: "",
                    showCheckbox: '',
                    showStick: '',
                    positionBT: '',
                    urlAddToCart: '',
                    urlAddToQuote: '',
                    urlAddMultipleToQuote: '',
                    priceIncludesTax: '',
                    targetArray: [],
                    existsDom: []
                },

                _init: function () {
                    if (this.options.jsonClassApply !== '') {
                        this._RenderForm();
                        this._RightButton();
                        this._updateAddToWishlistButton();
                    } else {
                        console.log('Multipleaddtocart: No input data received');
                    }
                },

                _create: function () {
                    var $widget = this;
                    var showstick = this.options.showStick;
                    var showcheckbox = this.options.showCheckbox;
                    // remove redirect-url
                    if ($('button.tocart').parent().find('.qty-m-c').length > 0) {
                        $('button.tocart').removeAttr('data-mage-init');
                    }

                    if (!showcheckbox) {
                        setTimeout(function () {
                            $('input[name="product-select[]"]').each(function () {
                                var form = $('#add-muntiple-product-' + $(this).data('froma'));
                                $widget._RenderOption(form, $(this).val());
                            })
                            $('.qty-m-c').trigger('input');
                        }, 0)
                    }

                    $widget._EventListener();

                    // trigger change form after popup showup
                    $('#bss_ajaxmuntiple_cart_popup').bind('contentUpdated', function () {
                        $('.option-er-pu input.bundle.option,.option-er-pu select.bundle.option').trigger('keyup');
                    });
                },

                _updateAddToWishlistButton: function () {
                    var self = this;
                    $('[data-action="add-to-wishlist"]').on('click', function (event) {
                        event.preventDefault();
                        var params = $(this).data('post'),
                            dataToAdd = {};

                        dataToAdd['qty'] = $(this).closest('.product-item-actions').find('.qty-m-c').val();
                        if (!params) {
                            params = {
                                'data': {}
                            };
                        }
                        params.data = $.extend({}, params.data, dataToAdd, {
                            'qty': $(this).closest('.product-item-actions').find('.qty-m-c').val()
                        });
                        $(this).data('post', params);
                    });
                },

                _RenderForm: function (config) {
                    var template_form = mageTemplate('#form-multiple-add');
                    var template_qty = mageTemplate('#qty-multiple-add');
                    var template_qty_hide_price = mageTemplate('#qty-multiple-add-hide-price');
                    var template_checkbox = mageTemplate('#checkbox-multiple-add');
                    var template_button = mageTemplate('#button-multiple-add');
                    var positionbs = this.options.positionBT;
                    var product_id_ad;

                    var self = this;

                    var jsonDisplayAddMultipleCart = this.options.jsonDisplayAddMultipleCart;
                    var jsonDisplayAddMultipleQuote = this.options.jsonDisplayAddMultipleQuote;

                    $.each(this.options.jsonClassApply, function (index, el) {
                        if ($(el).length) {
                            elementCss = el;
                            //add checkbox all
                            $(el).each(function (i) {
                                var existDomFunc = function (dom) {
                                        return dom == this;
                                    }.bind(this),
                                    isExistDom = self.options.existsDom.some(existDomFunc);
                                if (!isExistDom) {
                                    self.options.existsDom.push(this);
                                    var addMultiObject = {
                                            form: `separate-form-${index}_${i}`,
                                            btn: `separate-btn-${index}_${i}`,
                                            id: `${index}_${i}`
                                        },
                                        duplicateFormIdFunc = function (item) {
                                            return item.id == addMultiObject.id
                                        },
                                        isDuplicateFormId = self.options.targetArray.some(duplicateFormIdFunc);
                                    if (isDuplicateFormId) {
                                        var generatedNewId = `${index * 5}_${i * 5}`;
                                        addMultiObject.form = `separate-form-${generatedNewId}`;
                                        addMultiObject.btn = `separate-btn-${generatedNewId}`;
                                        addMultiObject.id = `${generatedNewId}`;
                                    }

                                    self.options.targetArray.push(addMultiObject);
                                    var form_addmuntiple = template_form({
                                        data: {
                                            id: 'add-muntiple-product-trim' + addMultiObject.id,
                                            class: `add-mt-${addMultiObject.id}`,
                                            name: 'add-muntiple-product-trim' + addMultiObject.id,
                                        }
                                    });

                                    var qty_addmuntiple = template_qty({
                                        data: {
                                            group: 'gr-add-mt-' + addMultiObject.id
                                        }
                                    });

                                    var displayAddMultipleCart = 1;
                                    if (jsonDisplayAddMultipleCart.search("," + elementCss + ",") < 0) {
                                        displayAddMultipleCart = 0;
                                    }

                                    displayAddMultipleQuote = 0;
                                    if (jsonDisplayAddMultipleQuote.search("," + elementCss + ",") >= 0) {
                                        displayAddMultipleQuote = 1;
                                    }

                                    var button_addmuntiple = template_button({
                                        data: {
                                            id: 'bt-ad-mt-' + `${addMultiObject.id}`,
                                            class: addMultiObject.btn,
                                            froma: 'trim' + addMultiObject.id,
                                            form_id: addMultiObject.id,
                                            displayAddMultipleCart: displayAddMultipleCart,
                                            displayAddMultipleQuote: displayAddMultipleQuote
                                        }
                                    });
                                    if ($(this).find('.actions-primary').length) {
                                        $(this).css('position', 'relative');
                                        $(this).addClass(`${addMultiObject.form} gr-add-mt-${addMultiObject.id}`);
                                        // add form
                                        if ($(this).find($('#add-muntiple-product-trim' + addMultiObject.id)).length === 0) {
                                            $(this).append(form_addmuntiple);

                                            // add button
                                            switch (positionbs) {
                                                case 0:
                                                    $(this).prepend(button_addmuntiple);
                                                    break;
                                                case 1:
                                                    $(this).append(button_addmuntiple);
                                                    break;
                                                case 2:
                                                    $(this).prepend(button_addmuntiple);
                                                    $(this).append(button_addmuntiple);
                                                    break;
                                                case 3:
                                                    $(this).append(button_addmuntiple).find('.addmanytocart').addClass('right-scroll').css({
                                                        position: 'absolute',
                                                        zIndex: '6',
                                                        top: '0px',
                                                        right: '0px'
                                                    });
                                                    self.options.targetArray = self.options.targetArray.map(function (item) {
                                                        var addMultiFormDom = $('.' + item.form);
                                                        if (addMultiFormDom.length && typeof item.formOffset !== undefined) {
                                                            item.formOffset = addMultiFormDom.offset().top;
                                                            item.formHeight = addMultiFormDom.outerHeight();
                                                            return item;
                                                        } else {
                                                            return item;
                                                        }
                                                    });
                                                // $(this).css('overflow','hidden');
                                            }
                                        }
                                    }
                                    // add box qty and check box
                                    $(this).find('.product-item').each(function () {
                                        if ($(this).find($('#product_' + product_id_ad)).length === 0
                                            && $(this).find($('[data-group]')).length === 0) {
                                            // Skip apply item clone item in owl slideshow
                                            if ($(this).parent().hasClass('cloned')) {
                                                return;
                                            }
                                            // ./end skip
                                            if ($(this).find('form').length) {
                                                if ($(this).find('input[name="product"]').length) {
                                                    product_id_ad = $(this).find('input[name="product"]').val();
                                                } else {
                                                    if ($(this).parents('.product.info').find('.price-box').data('product-id') != '') {
                                                        product_id_ad = $(this).parents('.product.info').find('.price-box').data('product-id');
                                                    }
                                                }
                                                if (product_id_ad != '') {
                                                    var checkbox_addmuntiple = template_checkbox({
                                                        data: {
                                                            id: 'product_' + product_id_ad,
                                                            class: 'product-select add-mt-' + addMultiObject.id,
                                                            froma: 'trim' + addMultiObject.id,
                                                            value: product_id_ad
                                                        }
                                                    });
                                                    $(this).find('button.tocart').before(checkbox_addmuntiple);
                                                    $(this).find('button.tocart').before(qty_addmuntiple);
                                                    if ($(this).find('button.tocart').length == 0 && $(this).find('div.quote-category').length > 0) {
                                                        var qtyAddMultipleHidePrice = template_qty_hide_price({
                                                            data: {
                                                                productHidePrice: product_id_ad
                                                            }
                                                        });
                                                        $(this).find('div.quote-category').before(checkbox_addmuntiple);
                                                        $(this).find('div.quote-category').before(qty_addmuntiple);
                                                        $(this).find('div.quote-category').before(qtyAddMultipleHidePrice);
                                                    }
                                                }
                                            } else {
                                                var $addToCartBtn = $(this).find('button.tocart'),
                                                    dataPost;
                                                if ($addToCartBtn.length) {
                                                    dataPost = $.parseJSON($addToCartBtn.attr('data-post')) || null;
                                                } else {
                                                    dataPost = null;
                                                }
                                                // prevent dataPost.js execute http post request
                                                $addToCartBtn.attr({
                                                    'bss-data-post': $addToCartBtn.attr('data-post')
                                                }).removeAttr('data-post');
                                                if (dataPost && dataPost.data.product) {
                                                    product_id_ad = dataPost.data.product;
                                                } else {
                                                    //old_code_and_dont-hiểu product_id_ad = $(this).parents('.product-item').find('.price-box').data('product-id');
                                                    product_id_ad = $(this).find('.price-box').data('product-id');
                                                }
                                                if (Math.floor(product_id_ad) == product_id_ad && $.isNumeric(product_id_ad)) {
                                                    var checkbox_addmuntiple = template_checkbox({
                                                        data: {
                                                            id: 'product_' + product_id_ad,
                                                            class: 'product-select add-mt-' + addMultiObject.id,
                                                            froma: 'trim' + addMultiObject.id,
                                                            value: product_id_ad
                                                        }
                                                    });
                                                    $(this).find('button.tocart').before(checkbox_addmuntiple);
                                                    $(this).find('button.tocart').before(qty_addmuntiple);
                                                }
                                            }
                                        }
                                    })
                                }
                            })
                        }
                    });
                },

                _RightButton: function () {
                    var self = this;
                    $(".addmanytocart.right-scroll").css({
                        position: 'absolute',
                        top: '0px',
                        zIndex: '6',
                        right: '0px'
                    });
                    if ($(".addmanytoquote.right-scroll").length > 0) {
                        $(".addmanytoquote.right-scroll").css({
                            position: 'absolute',
                            top: '0px',
                            zIndex: '6',
                            right: '220px'
                        });
                    }
                    this.options.targetArray = this.options.targetArray.map(function (item) {
                        var addMultiFormDom = $('.' + item.form);
                        if (addMultiFormDom.length) {
                            item.formOffset = addMultiFormDom.offset().top;
                            item.formHeight = addMultiFormDom.outerHeight();
                            return item;
                        } else {
                            return item;
                        }
                    });
                    $(window).scroll(function () {
                        self.options.targetArray.forEach(function (item) {
                            var $addAllBtnDom = $(`.${item.btn}`);
                            if ($addAllBtnDom.length) {
                                var bottomPosition = item.formOffset + item.formHeight;
                                if ($(window).scrollTop() >= item.formOffset && $(window).scrollTop() < bottomPosition) {
                                    $addAllBtnDom.css({
                                        top: $(window).scrollTop() + 15 - item.formOffset + 'px'
                                    });
                                } else if ($(window).scrollTop() >= bottomPosition) {
                                    $addAllBtnDom.css({
                                        top: `${item.formHeight}px`
                                    });
                                } else {
                                    $addAllBtnDom.css({
                                        top: '0px'
                                    });
                                }
                            }
                        });
                    });
                },

                _EventListener: function () {

                    var $widget = this;

                    // For porto with mageplaza ajax layered module compatible
                    $(document).ajaxComplete(function () {
                        if (window.isReRenderForm) {
                            $widget._RenderForm();
                        }
                    });
                    // ./END

                    $(document).on('click', '.add-all-product,.product-select', function () {
                        return $widget._OnClick($(this));
                    });

                    $(document).on('input', '.qty-m-c', function () {
                        return $widget._InputChange($(this));
                    });

                    $('body').on('change', '.product-item-actions input,.product-item-actions select , .product-item-actions textarea', function () {
                        return $widget._OnChange($(this));
                    });

                    $(".quote-category").click(
                        function (e) {
                            if(displayAddMultipleQuote) {
                                $(this).find(".toquote").attr("data-post", null);
                            }
                        }
                    )
                    $(document).on('click', '[class*="add-mt-"] ~ button.tocart,.quote-category,.addmanytocart,.addmanytocart-popup', function (e) {
                        if ($('button.addmanytocart').length) {
                            e.preventDefault();
                            return $widget._AddToCart($(this));
                        }
                    })
                    // popup
                    var decimalSymbol = $('#currency-add').val();
                    var priceIncludesTax = $widget.options.priceIncludesTax;

                    $('body').on("change paste keyup", '.option-er-pu input.product-custom-option,.option-er-pu select.product-custom-option , .option-er-pu textarea.product-custom-option', function () {
                        var productid = $(this).parents('.info-er-pu').find('.price-box').data('product-id');
                        var ratetax = $('#rate_' + productid).val();
                        return $widget._ReloadPriceCustomOption($(this), productid, decimalSymbol, ratetax, priceIncludesTax);
                    });
                    // bundel product
                    $('body').on("change paste keyup", '.option-er-pu input.bundle.option, .option-er-pu select.bundle.option, .option-er-pu input.qty.bundle, .info-er-pu input.quantity', function () {
                        var productid = $(this).parents('.info-er-pu').find('.price-box').data('product-id');
                        var ratetax = $('#rate_' + productid).val();
                        return $widget._ReloadPriceBundel($(this), productid, decimalSymbol, ratetax, priceIncludesTax);
                    });

                    // download
                    $('body').on("change paste keyup", '.option-er-pu .downloads input,.option-er-pu .downloads select', function () {
                        var productid = $(this).parents('.info-er-pu').find('.price-box').data('product-id');
                        var ratetax = $('#rate_' + productid).val();
                        return $widget._ReloadPriceDownloads($(this), productid, decimalSymbol, ratetax, priceIncludesTax);
                    });

                },

                _OnClick: function ($this) {
                    var $widget = this;
                    var showstick = this.options.showStick;
                    var showcheckbox = this.options.showCheckbox;
                    if ($this.hasClass('add-all-product')) {
                        var select = $this.parents('.button-bs-ad').find('button').attr('id').split('-');
                        var form = $('#add-muntiple-product-trim' + select[3]),
                            $checkAllBtn = $(`[data-formid="${select[3]}"]`);
                        if ($this.is(':checked')) {
                            // $('.add-all-product').prop("checked", true);
                            $checkAllBtn.prop("checked", true);
                            $('.add-mt-' + select[3]).each(function () {
                                if (!$(this).is(':checked')) {
                                    $(this).trigger('click');
                                }
                                $widget._RenderOption(form, $this.val());
                            })
                        } else {
                            // $('.add-all-product').prop("checked", false);
                            $checkAllBtn.prop("checked", false);
                            $('.add-mt-' + select[3]).each(function () {
                                if ($(this).is(':checked')) {
                                    $(this).trigger('click');
                                }
                                $(form).find('.vls_' + $this.val()).remove();
                            })
                        }
                    } else {
                        var total_qty = 0;
                        var total_product = 0;
                        var total_qty_quote = 0;
                        var total_product_quote = 0;
                        var product_id = $this.val();
                        var form = $('#add-muntiple-product-' + $this.data('froma'));
                        if ($this.is(':checked')) {
                            $widget._RenderOption(form, product_id);
                            if (showstick) {
                                if ($this.siblings('.qty-m-c').val() > 0) {
                                    $this.parents('.product-item').css('position', 'relative');
                                    $this.parents('.product-item').prepend('<div class="ad-mt-stick"></div>');
                                } else {
                                    $this.parents('.product-item').css('position', '');
                                    $this.parents('.product-item').find('.ad-mt-stick').remove();
                                }
                            }
                        } else {
                            $(form).find('.vls_' + product_id).remove();
                            $this.parents('.product-item').css('position', '');
                            $this.parents('.product-item').find('.ad-mt-stick').remove();
                        }
                        var _select = $this.siblings('.qty-m-c').data('group').split('-');
                        if ($('.gr-add-mt-' + _select[3]).length) {
                            var $qtyBox = $('.gr-add-mt-' + _select[3]).find('.qty-m-c'),
                                totalProducts = 0;
                            $qtyBox.each(function () {
                                if ($(this).parents('.add-option').length === 0) {
                                    if ($(this).val() && $(this).val() > 0) {
                                        if (showcheckbox) {
                                            if ($(this).siblings('input[name="product-select[]"]').is(':checked')) {
                                                if (!$(this).siblings().hasClass("hide-price")) {
                                                    total_qty += parseInt($(this).val());
                                                    total_product += 1;
                                                }
                                                if ($(this).siblings().hasClass("quote-category")) {
                                                    total_qty_quote += parseInt($(this).val());
                                                    total_product_quote += 1;
                                                }
                                            }
                                        } else {
                                            if (!$(this).siblings().hasClass("hide-price")) {
                                                total_qty += parseInt($(this).val());
                                                total_product += 1;
                                            }
                                            if ($(this).siblings().hasClass("quote-category")) {
                                                total_qty_quote += parseInt($(this).val());
                                                total_product_quote += 1;
                                            }
                                        }
                                    }
                                    totalProducts++;
                                }
                            });
                            if (totalProducts === total_product) {
                                $(`#checkall-${_select[3]}`).prop("checked", true);
                            } else {
                                $(`#checkall-${_select[3]}`).prop("checked", false);
                            }
                            $('.button-bs-ad button#bt-ad-mt-' + _select[3] + ' .total_qty span').text(total_qty);
                            $('.button-bs-ad button#bt-ad-mt-' + _select[3] + ' .total_products span').text(total_product);
                            $('.button-bs-ad button#bt-ad-mt-' + _select[3] + ' .total_qty_quote span').text(total_qty_quote);
                            $('.button-bs-ad button#bt-ad-mt-' + _select[3] + ' .total_products_quote span').text(total_product_quote);
                        }
                    }
                },

                _OnChange: function ($this) {
                    var product_id = $this.parent().find('input[name="product-select[]"]').val();
                    var form = $('#add-muntiple-product-' + $this.parent().find('input[name="product-select[]"]').data('froma'));
                    var name = product_id + '_' + $this.attr('name');
                    if ($this.is("input")) {
                        $(form).find(".add-option").find('input[name="' + name + '"]').val($this.val());
                    }
                    if ($this.is("select")) {
                        $(form).find(".add-option").find('select[name="' + name + '"]').val($this.val());
                    }
                    if ($this.is("textarea")) {
                        $(form).find(".add-option").find('textarea[name="' + name + '"]').val($this.val());
                    }
                },

                _InputChange: function ($this) {
                    var $widget = this;
                    var showstick = this.options.showStick;
                    var showcheckbox = this.options.showCheckbox;
                    var total_qty = 0;
                    var total_product = 0;
                    var total_qty_quote = 0;
                    var total_product_quote = 0;
                    if ($this.parents('.' + $this.data('group')).length) {
                        $this.parents('.' + $this.data('group')).find('.qty-m-c').each(function () {
                            if ($(this).parents('.add-option').length === 0) {
                                if ($(this).val() && $(this).val() > 0) {
                                    if (showcheckbox) {
                                        if ($(this).siblings('input[name="product-select[]"]').is(':checked')) {
                                            if (!$(this).siblings().hasClass("hide-price")) {
                                                total_qty += parseInt($(this).val());
                                                total_product += 1;
                                            }
                                            if ($(this).siblings().hasClass("quote-category")) {
                                                total_qty_quote += parseInt($(this).val());
                                                total_product_quote += 1;
                                            }
                                            if (showstick) {
                                                $(this).parents('.product-item').css('position', 'relative');
                                                $(this).parents('.product-item').prepend('<div class="ad-mt-stick"></div>');
                                            }
                                        } else {
                                            $(this).parents('.product-item').css('position', '');
                                            $(this).parents('.product-item').find('.ad-mt-stick').remove();
                                        }
                                    } else {
                                        if (!$(this).siblings().hasClass("hide-price")) {
                                            total_qty += parseInt($(this).val());
                                            total_product += 1;
                                        }
                                        if ($(this).siblings().hasClass("quote-category")) {
                                            total_qty_quote += parseInt($(this).val());
                                            total_product_quote += 1;
                                        }
                                        if (showstick) {
                                            $(this).parents('.product-item').css('position', 'relative');
                                            $(this).parents('.product-item').prepend('<div class="ad-mt-stick"></div>');
                                        }
                                    }
                                } else {
                                    $(this).parents('.product-item').css('position', '');
                                    $(this).parents('.product-item').find('.ad-mt-stick').remove();
                                }
                            }
                        })
                        var bt = $this.data('group').split('-');
                        $('.button-bs-ad button#bt-ad-mt-' + bt[3] + ' .total_qty span').text(total_qty);
                        $('.button-bs-ad button#bt-ad-mt-' + bt[3] + ' .total_products span').text(total_product);
                        $('.button-bs-ad button#bt-ad-mt-' + bt[3] + ' .total_qty_quote span').text(total_qty_quote);
                        $('.button-bs-ad button#bt-ad-mt-' + bt[3] + ' .total_products_quote span').text(total_product_quote);
                    }
                },

                _RenderOption: function (form, product_id) {
                    $('#product_' + product_id).parent().find('input').each(function () {
                        if ($(this).attr('name') != 'uenc' && $(this).attr('name') != 'form_key' && $(this).attr('name') != 'product') {
                            var name = product_id + '_' + $(this).attr('name');
                            if ($(this).attr('name') == 'product-select[]' && ($(form).find('#product_' + product_id).length == 0 || $(this).parents('.wishlist').length)) {
                                $(this).clone().prependTo($(form).find(".add-option")).addClass('vls_' + product_id).val($(this).val());
                            } else {
                                if ($(form).find('input[name="' + name + '"]').length == 0) {
                                    $(this).clone().prependTo($(form).find(".add-option")).addClass('vls_' + product_id).attr('name', name).val($(this).val())
                                        .attr('product_id', product_id).removeAttr('id');
                                }
                            }
                        }
                    })
                    $('#product_' + product_id).parent().find('textarea').each(function () {
                        var name = product_id + '_' + $(this).attr('name');
                        if ($(form).find('textarea[name="' + name + '"]').length == 0) {
                            $(this).clone().prependTo($(form).find(".add-option")).addClass('vls_' + product_id).attr('name', name).val($(this).val())
                                .attr('product_id', product_id).removeAttr('id');
                        }
                    })
                    $('#product_' + product_id).parent().find('select').each(function () {
                        var name = product_id + '_' + $(this).attr('name');
                        if (!$(form).find('select[name="' + name + '"]').length == 0) {
                            $(this).clone().prependTo($(form).find(".add-option")).addClass('vls_' + product_id).attr('name', name).val($(this).val())
                                .attr('product_id', product_id).removeAttr('id');
                        }
                    })
                },
                // Popup
                _ReloadPriceCustomOption: function ($this, productId, decimalSymbol, tax, priceIncludesTax) {
                    var priceplus = 0;
                    var allselected = '';
                    var itemp = $('.er-pu-' + productId);
                    $(itemp).find('input').each(function () {
                        if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
                            if ($(this).is(':checked')) {
                                if ($(this).attr('price') > 0) {
                                    priceplus += parseFloat($(this).attr('price'));
                                }
                            }
                        }

                        if ($(this).attr('type') == 'text' || $(this).attr('type') == 'time') {
                            if ($(this).val() != '') {
                                if ($(this).attr('price') > 0) {
                                    priceplus += parseFloat($(this).attr('price'));
                                }
                            }
                        }
                        if ($(this).attr('type') == 'file') {
                        }
                    })

                    $(itemp).find('select').each(function () {
                        if ($(this).is("select[multiple]")) {
                            if ($(this).val() != '') {
                                $(this).find('option:selected').each(function () {
                                    if ($(this).attr('price') > 0) {
                                        priceplus += parseFloat($(this).attr('price'));
                                    }
                                });
                            }
                        } else if ($(this).hasClass('datetime-picker')) {
                            allselected = 1;
                            $(this).parent().find('select').each(function () {
                                if ($(this).val() == '') {
                                    allselected = 0;
                                }
                            })
                            if (allselected == 1) {
                                if ($('option:selected', this).attr('price') > 0) {
                                    priceplus += parseFloat($('option:selected', this).attr('price'));
                                }
                            }
                        } else {
                            if ($(this).val() != '') {
                                if ($('option:selected', this).attr('price') > 0) {
                                    priceplus += parseFloat($('option:selected', this).attr('price'));
                                }
                            }
                        }
                    })

                    $(itemp).find('textarea.product-custom-option').each(function () {
                        if ($(this).val() != '') {
                            if ($(this).attr('price') > 0) {
                                priceplus += parseFloat($(this).attr('price'));
                            }
                        }
                    })

                    var finalPrice = $this.parents('.info-er-pu').find('.fixed-price-ad-pu span.finalPrice').text();
                    if (!finalPrice) {
                        finalPrice = itemp.find('#product_price').val();
                    }
                    finalPrice = finalPrice.replace(decimalSymbol, '');
                    finalPrice = parseFloat(finalPrice);
                    var basePrice = $this.parents('.info-er-pu').find('.fixed-price-ad-pu span.basePrice').text();
                    basePrice = basePrice.replace(decimalSymbol, '');
                    basePrice = parseFloat(basePrice);
                    var oldPrice = $this.parents('.info-er-pu').find('.fixed-price-ad-pu span.oldPrice').text();
                    oldPrice = oldPrice.replace(decimalSymbol, '');
                    oldPrice = parseFloat(oldPrice);

                    if (priceIncludesTax == '1') {
                        if (tax && tax > 0) {
                            finalPrice = finalPrice + priceplus;
                            basePrice = basePrice + parseFloat((priceplus - priceplus * (1 - 1 / (1 + parseFloat(tax)))).toFixed(2));
                            oldPrice = oldPrice + priceplus;
                        } else {
                            basePrice = basePrice + priceplus;
                            finalPrice = finalPrice + priceplus;
                            oldPrice = oldPrice + priceplus;
                        }
                    } else {
                        if (tax && tax > 0) {
                            finalPrice = (parseFloat(finalPrice + priceplus) + (parseFloat(priceplus) * (parseFloat(tax)))).toFixed(2);
                            basePrice = basePrice + priceplus;
                            oldPrice = oldPrice + priceplus;
                        } else {
                            finalPrice = finalPrice + priceplus;
                            basePrice = basePrice + priceplus;
                            oldPrice = oldPrice + priceplus;
                        }
                    }

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="maxPrice"] > .price').text($('#currency-add').val() + parseFloat(finalPrice).toFixed(2));

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="finalPrice"] > .price').text($('#currency-add').val() + parseFloat(finalPrice).toFixed(2));

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="basePrice"] > .price').text($('#currency-add').val() + parseFloat(basePrice).toFixed(2));

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="oldPrice"] > .price').text($('#currency-add').val() + parseFloat(oldPrice).toFixed(2));
                    // return priceplus;
                },

                _ReloadPriceBundel: function ($this, productId, decimalSymbol, tax, priceIncludesTax) {
                    var price = 0;
                    var price_ect = 0;
                    var itemp = $('.er-pu-' + productId);
                    var product_price = parseFloat($('.er-pu-' + productId).find('#product_price').val());

                    $(itemp).find('input').each(function () {
                        var qty_e = $(this).parents('.field.option').find('input.qty');

                        if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
                            if ($(this).is(':checked')) {
                                if (parseInt($(this).attr('can-change-qty')) === 0) {
                                    qty_e.attr('disabled', true).val($(this).attr('default-qty'));
                                } else {
                                    qty_e.removeAttr('disabled');
                                }
                                var qty = (qty_e.is(':disabled') || qty_e.length === 0) ? 1 : (parseInt(qty_e.val()) || 0);

                                if ($(this).attr('price') > 0) {
                                    price += parseFloat($(this).attr('price')) * qty;
                                    if (tax && tax > 0) {
                                        price_ect += parseFloat((parseFloat($(this).attr('price')) - (parseFloat($(this).attr('price')) * (1 - 1 / (1 + parseFloat(tax))))).toFixed(2)) * qty;
                                    } else {
                                        price_ect += parseFloat((parseFloat($(this).attr('price'))).toFixed(2)) * qty;
                                    }
                                }
                            }
                        }
                    })

                    $(itemp).find('select').each(function () {
                        var qty_e = $(this).parents('.field.option').find('input.qty');

                        if ($(this).is("select[multiple]")) {
                            // price has included quantity itself
                            if ($(this).val() != '') {
                                $(this).find('option:selected').each(function () {
                                    if ($(this).attr('price') > 0) {
                                        price += parseFloat($(this).attr('price'));
                                        if (tax && tax > 0) {
                                            price_ect += parseFloat((parseFloat($(this).attr('price')) - (parseFloat($(this).attr('price')) * (1 - 1 / (1 + parseFloat(tax))))).toFixed(2));
                                        } else {
                                            price_ect += parseFloat((parseFloat($(this).attr('price'))).toFixed(2));
                                        }
                                    }
                                });
                            }
                        } else {
                            if ($(this).val() != '') {
                                if (parseInt($('option:selected', this).attr('can-change-qty')) === 0) {
                                    qty_e.attr('disabled', true).val($('option:selected', this).attr('default-qty'));
                                } else {
                                    qty_e.removeAttr('disabled');
                                }
                                var qty = (qty_e.is(':disabled') || qty_e.length === 0) ? 1 : (parseInt(qty_e.val()) || 0);

                                if ($('option:selected', this).attr('price') > 0) {
                                    price += parseFloat($('option:selected', this).attr('price')) * qty;
                                    if (tax && tax > 0) {
                                        price_ect += parseFloat((parseFloat($('option:selected', this).attr('price')) - (parseFloat($('option:selected', this).attr('price')) * (1 - 1 / (1 + parseFloat(tax))))).toFixed(2)) * qty;
                                    } else {
                                        price_ect += parseFloat((parseFloat($('option:selected', this).attr('price'))).toFixed(2)) * qty;
                                    }
                                }
                            }
                        }
                    })

                    // add bundle product price
                    if (priceIncludesTax == '1') {
                        if (tax && tax > 0) {
                            price += product_price;
                            price_ect += parseFloat((product_price - product_price * (1 - 1 / (1 + parseFloat(tax)))).toFixed(2));
                        } else {
                            price += product_price;
                            price_ect += product_price
                        }
                    } else {
                        if (tax && tax > 0) {
                            price += product_price + parseFloat(product_price * parseFloat(tax).toFixed(2));
                            price_ect += product_price;
                        } else {
                            price += product_price;
                            price_ect += product_price;
                        }
                    }

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="maxPrice"] > .price').text($('#currency-add').val() + (parseFloat(price)).toFixed(2))

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="basePrice"] > .price').text($('#currency-add').val() + (parseFloat(price_ect)).toFixed(2))

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="oldPrice"] > .price').text($('#currency-add').val() + (parseFloat(price)).toFixed(2))
                },

                _ReloadPriceDownloads: function ($this, productId, decimalSymbol, tax, priceIncludesTax) {
                    var price = 0;
                    var price_ect = 0;
                    var itemp = $('.er-pu-' + productId);
                    $(itemp).find('input').each(function () {
                        if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
                            if ($(this).is(':checked')) {
                                if ($(this).parent().find("span[data-price-type='']").length > 0) {
                                    if ($(this).parent().find("span[data-price-type='']").first().attr('data-price-amount') > 0) {
                                        price += parseFloat($(this).parent().find("span[data-price-type='']").first().attr('data-price-amount'));
                                        if (tax && tax > 0) {
                                            price_ect += parseFloat((parseFloat($(this).parent().find("span[data-price-type='']").first().attr('data-price-amount')) - (parseFloat($(this).parent().find("span[data-price-type='']").first().attr('data-price-amount')) * (1 - 1 / (1 + parseFloat(tax))))).toFixed(2));
                                        } else {
                                            price_ect += parseFloat((parseFloat($(this).parent().find("span[data-price-type='']").first().attr('data-price-amount'))).toFixed(2));
                                        }
                                    }
                                }
                            }
                        }
                    })

                    var finalPrice = $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="finalPrice"]').attr('data-price-amount');
                    finalPrice = parseFloat(finalPrice);
                    var basePrice = $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="basePrice"]').attr('data-price-amount');
                    basePrice = parseFloat(basePrice);
                    var oldPrice = $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="oldPrice"]').attr('data-price-amount');
                    oldPrice = parseFloat(oldPrice);

                    finalPrice += price;
                    basePrice += price_ect;
                    oldPrice += price;

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="finalPrice"] > .price').text($('#currency-add').val() + (parseFloat(finalPrice)).toFixed(2))

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="basePrice"] > .price').text($('#currency-add').val() + (parseFloat(basePrice)).toFixed(2))

                    $this.parents('.info-er-pu').find('.price-box .price-container> span[data-price-type="oldPrice"] > .price').text($('#currency-add').val() + (parseFloat(oldPrice)).toFixed(2))
                },

                _AddToCart: function ($this) {
                    var $widget = this,
                        form = $this.parents('form').get(0),
                        addUrl = this.options.urlAddToCart,
                        addUrlToQuote = this.options.urlAddToQuote,
                        urlAddMultipleToQuote = this.options.urlAddMultipleToQuote,
                        data,
                        dataPost,
                        qty = 0,
                        totalQty = 0,
                        product_id;
                    if ($this.hasClass('tocart') || $this.hasClass("quote-category")) {
                        if ($this.hasClass("quote-category")) {
                            addUrl = addUrlToQuote;
                        }
                        qty = $this.siblings('.qty-m-c').val();
                        $this.siblings('.qty-m-c').removeClass('mage-error');
                        if (qty < 0 || qty == 0) {
                            $this.siblings('.qty-m-c').addClass('mage-error');
                            return false;
                        }
                        if (form && $(form).attr('id') != 'wishlist-view-form') {
                            data = $(form).serialize();
                            $widget._sendAjax(addUrl, data);
                        } else {
                            dataPost = $.parseJSON($this.attr('bss-data-post'));
                            if (dataPost && dataPost.data.product) {
                                product_id = dataPost.data.product;
                            } else if ($this.parents('.product-item').find('input[name="product"]').first().val() != '') {
                                product_id = $this.parents('.product-item').find('input[name="product"]').first().val();
                            } else {
                                product_id = $this.parents('.product-item').find('.price-box').first().data('product-id');
                            }

                            if (Math.floor(product_id) == product_id && $.isNumeric(product_id)) {
                                // old code data +='&product=' + product_id + '&qty=' + qty;
                                data = {
                                    product: product_id,
                                    qty: qty
                                };
                                $widget._sendAjax(addUrl, data);
                                return false;
                            }
                        }
                    }
                    if ($this.hasClass('addmanytocart')) {
                        var quoteExtension = 0;
                        var action = "allows add to cart.";
                        if ($this.hasClass('addmanytoquote')) {
                            action = "allows add to quote.";
                            quoteExtension = 1;
                        }
                        form = $('#add-muntiple-product-' + $this.data('froma'));
                        if ($(form).find('.product-select').length == 0) {
                            mAlert({
                                    title: $.mage.__('Notice'),
                                    content: $.mage.__('Please select product ' + action)
                                }
                            );
                            return false;
                        }
                        $(form).find('.qty-m-c').each(function () {
                            var productId = $(this).attr("product_id")
                            var classQuoteExtension = productId + "_quote_extension";
                            if ($(this).val() > 0) {
                                if (quoteExtension) {
                                    if ($(this).siblings().hasClass('' + classQuoteExtension)) {
                                        totalQty += $(this).val();
                                    }
                                } else if (!$(this).siblings().hasClass('qty-m-c-h-p-' + productId)) {
                                    totalQty += $(this).val();
                                }

                            }
                        });
                        if (totalQty < 0 || totalQty == 0) {
                            mAlert({
                                title: $.mage.__('Notice'),
                                content: $.mage.__('Please choose quantity greather than 0 for at least 1 selected item ' + action)
                            });
                            return false;
                        }
                        data = $(form).serialize();
                        addUrl = $(form).attr('action');
                        if ($this.hasClass('addmanytoquote')) {
                            addUrl = urlAddMultipleToQuote
                        }
                        $widget._sendAjax(addUrl, data);
                        return false;
                    }
                    if ($this.hasClass('addmanytocart-popup')) {
                        var dataForm = $('#product_addmuntile_form_popup');
                        dataForm.mage('validation', {});
                        form = $this.parents('form').get(0);
                        if ($(dataForm).valid()) {
                            $('.fancybox-opened').css('zIndex', '1');
                            addUrl = $(form).attr('action');
                            if ($this.hasClass('addmanytoquote-popup')) {
                                addUrl = urlAddMultipleToQuote + "popup/1"
                            }
                            data = $(form).serialize();
                            $widget._sendAjax(addUrl, data);
                        }
                        return false;
                    }
                },

                _sendAjax: function (addUrl, data) {
                    var $widget = this;
                    $.fancybox.showLoading();
                    $.fancybox.helpers.overlay.open({parent: 'body'});
                    $.ajax(
                        {
                            type: 'post',
                            url: addUrl,
                            data: data,
                            dataType: 'json',
                            success: function (data) {
                                // Remove the tick after the customer has added the product to cart
                                if (data.addCartSuccess) {
                                    $(".button-bs-ad .add-all-product").each(function () {
                                        $(this).click();
                                        if ($(this).is(':checked')) {
                                            $(this).click();
                                        }
                                    });
                                }
                                if (data.popup) {
                                    $('#bss_ajaxmuntiple_cart_popup').html(data.popup);
                                    $('#bss_ajaxmuntiple_cart_popup').trigger('contentUpdated');
                                    $.fancybox({
                                        href: '#bss_ajaxmuntiple_cart_popup',
                                        modal: false,
                                        helpers: {
                                            overlay: {
                                                locked: false
                                            }
                                        },
                                        afterClose: function () {
                                        }
                                    });
                                } else {
                                    $.fancybox.hideLoading();
                                    $('.fancybox-overlay').hide();
                                    return false;
                                }
                            },
                            error: function (xhr, status, error) {
                                $.fancybox.hideLoading();
                                $('.fancybox-overlay').hide();
                                return false;
                                // window.location.href = '';
                            }
                        }
                    );
                }
            }
        );
        return $.mage.Multipleaddtocart;
    }
);
