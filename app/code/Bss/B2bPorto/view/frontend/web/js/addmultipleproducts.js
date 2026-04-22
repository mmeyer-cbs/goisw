define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('bss.b2bPorto_addmultipleproducts', {
        options: {
            isAjaxLayerEnabled: false,
        },
        _create: function () {
            if (!!+this.options.isAjaxLayerEnabled) {
                window.isReRenderForm = true;
            }
        }
    });
    return $.bss.b2bPorto_addmultipleproducts;
});
