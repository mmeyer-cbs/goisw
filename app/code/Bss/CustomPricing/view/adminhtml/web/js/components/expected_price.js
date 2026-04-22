define([
    'Magento_Ui/js/form/element/abstract',
    'mage/translate',
    'Bss_CustomPricing/js/model/source/priceType',
    'Magento_Catalog/js/price-utils'
], function (Element, $t, priceType, priceUtils) {
    'use strict';

    return Element.extend({
        defaults: {
            elementTmpl: 'Bss_CustomPricing/form/element/expected-price',
            isUndefined: true,
            priceType: 1,
            priceValue: null,
            imports: {
                basePriceFormat: '${ $.provider }:data.basePriceFormat',
                originPrice: '${ $.provider }:data.origin_price'
            }
        },

        /**
         * Observe imported variables, subscribe imported changed value
         *
         * @returns {*}
         */
        initObservable: function () {
            this._super().observe('isUndefined priceType priceValue');
            this.priceType.subscribe(this.handleExpectedPrice, this);
            this.priceValue.subscribe(this.handleExpectedPrice, this);
            return this;
        },

        /**
         * Handling the expected price base on price type and price value
         *
         * @param value
         */
        handleExpectedPrice: function (value) {
            var expectedPrice = undefined,
                originPrice = parseFloat(this.originPrice),
                priceValue = parseFloat(this.priceValue());

            switch (parseInt(this.priceType())) {
                case priceType['absolute_price']:
                    expectedPrice = priceValue;
                    break;
                case priceType['increase_fixed_price']:
                    expectedPrice = originPrice + priceValue
                    break;
                case priceType['decrease_fixed_price']:
                    if (originPrice <= priceValue) {
                        expectedPrice = 0;
                    } else {
                        expectedPrice = originPrice - priceValue;
                    }
                    break;
                case priceType['increase_percent_price']:
                    expectedPrice = originPrice * (100 + priceValue)/100
                    break;
                case priceType['decrease_percent_price']:
                    expectedPrice = originPrice - (originPrice * (priceValue/100));
                    if (expectedPrice < 0) {
                        expectedPrice = 0;
                    }
                    break;
                default:
                    break;
            }
            this.value(priceUtils.formatPrice(expectedPrice, this.basePriceFormat));
        },

        /**
         * If the product price has no price value, then set Expected Price is Undefined
         *
         * @param value
         * @returns {*}
         */
        normalizeData: function (value) {
            if (!value) {
                value = $t('Undefined')
            }

            return this._super(value);
        },

        /**
         * Get formatted title
         *
         * @returns {*}
         */
        getTitle: function () {
            return $t('Expected Price') + ': ' + this.value();
        }
    });
});
