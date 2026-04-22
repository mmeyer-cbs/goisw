define([
        'jquery',
    ],
    function ($) {
        'use strict';
        var mixin = {
            saveAddress: function (responseData, data) {
                data["custom_attributes_address"] = responseData.custom_attributes_address
                this._super();
            }
        };

        return function (target) { // target == Result that Magento_Ui/.../columns returns.
            return target.extend(mixin); // new result that all other modules receive
        };
    });
