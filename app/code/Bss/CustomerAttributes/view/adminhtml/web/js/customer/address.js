define(
    [
        "jquery",
        'mage/url'
    ],
    function ($) {
        return function (config) {
            window.urlCustomAttributesAddress = config.urlCustomAttributesAddress;
        }
    }
);
