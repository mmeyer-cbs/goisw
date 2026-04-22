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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'ko',
    'Magento_Ui/js/form/element/file-uploader',
    'Bss_CustomerAttributes/js/model/attribute-dependent',
    'Bss_CustomerAttributes/js/model/mixin'
], function (ko, File, AttributeDependentProcessor,Mixin) {
    'use strict';

    return File.extend({
        initObservable() {
            this._super();
            AttributeDependentProcessor.workingElement.subscribe(Mixin.onWorkingEleChanged, this);
            return this;
        },
        onUpdate()
        {
            this._super();
            AttributeDependentProcessor.processAttributeVisibility(this.value(), this.attributeDependent);
        }
    });
});
