<?php
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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Model;

use Bss\Paymentshipping\Api\PaymentshippingManagementInterface;
use Bss\Paymentshipping\Helper\Data as HelperData;

/**
 * Class PaymentshippingManagement
 *
 * @package Bss\Paymentshipping\Model
 */
class PaymentshippingManagement implements PaymentshippingManagementInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * PaymentshippingManagement constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Get config module
     *
     * @param int $websiteId
     * @return array
     */
    public function getConfig($storeViewId = null)
    {
        $result["module_configs"]["enable_payment"] = $this->helperData->isEnablePayment($storeViewId);
        $result["module_configs"]["enable_shipping"] = $this->helperData->isEnableShipping($storeViewId);
        return $result;
    }
}
