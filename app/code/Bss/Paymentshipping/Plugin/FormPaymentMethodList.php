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

class FormPaymentMethodList
{
    /**
     * @var \Bss\Paymentshipping\Helper\Data
     */
    protected $bssHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * FormPaymentMethodList constructor.
     * @param \Bss\Paymentshipping\Helper\Data $bssHelper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\Paymentshipping\Helper\Data $bssHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->bssHelper = $bssHelper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Payment\Block\Form\Container $subject
     * @param array $methods
     * @return mixed
     */
    public function afterGetMethods(\Magento\Payment\Block\Form\Container $subject, $methods)
    {
        $myHelperData = $this->bssHelper;

        foreach ($methods as $key => $method) {
            if ($subject->getQuote()->getCustomerGroupId()) {
                if (!$myHelperData->canUseMethod(
                    $method->getCode(),
                    'payment',
                    $subject->getQuote()->getStore()->getWebsiteId(),
                    $subject->getQuote()->getCustomerGroupId()
                )) {
                    unset($methods[$key]);
                }
            } else {
                if (!$myHelperData->canUseMethod($method->getCode(), 'payment', $subject->getQuote()->getStore()->getWebsiteId())) {
                    unset($methods[$key]);
                }
            }
        }

        return $methods;
    }
}
