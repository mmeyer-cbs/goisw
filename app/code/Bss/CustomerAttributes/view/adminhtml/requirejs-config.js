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
            "Magento_Customer/js/form/components/insert-form" : {
                "Bss_CustomerAttributes/js/customer/form/components/insert-form": true
            },
            "Magento_Customer/js/form/components/insert-listing" : {
                "Bss_CustomerAttributes/js/customer/form/components/insert-listing": true
            },
        }
    },
    "map": {
        "*": {
            "Magento_Customer/template/default-address.html":
                "Bss_CustomerAttributes/template/default-address.html",
        }
    }
};
