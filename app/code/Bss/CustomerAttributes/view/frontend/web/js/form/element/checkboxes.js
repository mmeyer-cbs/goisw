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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerAttributes/js/model/attribute-dependent',
    'Bss_CustomerAttributes/js/model/mixin'
], function (ko, _, utils, Abstract, AttributeDependentProcessor,Mixin) {
    'use strict';

    return Abstract.extend({

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            var defaultValue = this.default;

            this._super();
            AttributeDependentProcessor.workingElement.subscribe(Mixin.onWorkingEleChanged, this);
            var value = this.default;

            this.value = ko.observableArray([]).extend(value);
            this.value(this.normalizeData(defaultValue));
            this.observe('options');
            return this;
        },

        onUpdate()
        {
            this._super();
            AttributeDependentProcessor.processAttributeVisibility(this.value(), this.attributeDependent);
        },

        /**
         * Splits incoming string value.
         *
         * @returns {Array}
         */
        normalizeData: function (value) {
            if (utils.isEmpty(value)) {
                value = [];
            }

            return _.isString(value) ? value.split(',') : value;
        },

        /**
         * Defines if value has changed
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var value = this.value(),
                initial = this.initialValue;

            return !utils.equalArrays(value, initial);
        },
    });
});
