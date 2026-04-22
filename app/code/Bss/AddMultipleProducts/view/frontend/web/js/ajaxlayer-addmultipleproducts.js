define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('bss.ajaxlayer_addmultipleproducts', {
        options: {
            isAjaxLayerEnabled: false,
        },
        _create: function () {
            if (!!+this.options.isAjaxLayerEnabled) {
                window.isReRenderForm = true;
            }
        }
    });
    return $.bss.ajaxlayer_addmultipleproducts;
});
