define([
        'jquery',
    ],
    function ($) {
        'use strict';
        var mixin = {
            /**
             * On action call
             *
             * @param {Object} data - customer address and actions
             */
            onAction: function (data) {
                var thisOnAction = this;
                if(data.data && data.data.entity_id) {
                    $.ajax({
                        url: window.urlCustomAttributesAddress,
                        data: {
                            "entity_id": data.data.entity_id
                        },
                        type: "POST",
                        dataType: 'json'
                    }).done(function (response) {
                        data.data.custom_attributes_address = response.custom_attributes_address;
                        thisOnAction[data.action + 'Action'].call(thisOnAction, data.data);
                    });
                } else {
                    this._super();
                }
            }
        };

        return function (target) { // target == Result that Magento_Ui/.../columns returns.
            return target.extend(mixin); // new result that all other modules receive
        };
    });
