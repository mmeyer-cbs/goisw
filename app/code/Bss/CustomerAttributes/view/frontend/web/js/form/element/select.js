define([
    'ko',
    'Magento_Ui/js/form/element/select',
    'Bss_CustomerAttributes/js/model/attribute-dependent',
    'Bss_CustomerAttributes/js/model/mixin'
], function (ko, Select, AttributeDependentProcessor,Mixin) {
    'use strict';

    return Select.extend({
        initObservable() {
            this._super();
            AttributeDependentProcessor.workingElement.subscribe(Mixin.onWorkingEleChanged, this);
            return this;
        },

        onWorkingEleChanged: function (newVa) {
            if (newVa.value !== this.index) {
                return false;
            }
            if (newVa.activeParentCount > 0) {
                this.visible(true);
            } else {
                this.visible(false);
            }
            this.options(newVa.dependent_attribute_value);
        },

        onUpdate()
        {
            this._super();
            AttributeDependentProcessor.processAttributeVisibility(this.value(), this.attributeDependent);
        }
    });
});
