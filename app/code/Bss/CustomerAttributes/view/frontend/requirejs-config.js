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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
    config: {
        mixins: {
            "Magento_Ui/js/form/element/file-uploader" : {
                "Bss_CustomerAttributes/js/form/element/file-uploader": true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Bss_CustomerAttributes/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Bss_CustomerAttributes/js/action/set-billing-address': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Bss_CustomerAttributes/js/action/set-billing-address': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Bss_CustomerAttributes/js/action/set-billing-address': true
            },
            'Magento_Checkout/js/model/new-customer-address' : {
                'Bss_CustomerAttributes/js/model/new-customer-addresses-mixin' : true
            }
        }
    }
};
