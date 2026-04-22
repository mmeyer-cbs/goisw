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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

var config = {
    map: {
        '*': {
            'Magento_OfflinePayments/template/payment/purchaseorder-form.html':
                'Bss_CompanyCredit/template/payment/purchaseorder-form.html'
        }
    },

    config: {
        mixins: {
            "Magento_OfflinePayments/js/view/payment/method-renderer/purchaseorder-method" : {
                "Bss_CompanyCredit/js/view/payment/method-renderer/purchaseorder-method": true
            }
        }
    }
};
