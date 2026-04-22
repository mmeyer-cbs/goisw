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
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Plugin;

class PaymentMethodList
{
    /**
     * @var \Bss\Paymentshipping\Helper\Data
     */
    protected $bssHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Bss\Paymentshipping\Helper\Data $bssHelper
    ) {
        $this->bssHelper = $bssHelper;
    }

    /**
     * @param \Magento\Payment\Model\MethodList $subject
     * @param array $paymentMethodList
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(\Magento\Payment\Model\MethodList $subject, $paymentMethodList)
    {
        $myHelperData = $this->bssHelper;

        foreach ($paymentMethodList as $key => $method) {
            if (!$myHelperData->canUseMethod($method->getCode(), 'payment')) {
                unset($paymentMethodList[$key]);
            }
        }

        return $paymentMethodList;
    }
}
