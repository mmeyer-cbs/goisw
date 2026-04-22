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
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Plugin\Quote\Model\Quote\Validator\MinimumOrderAmount;

/**
 * Class ValidationMessage
 *
 * @package Bss\OrderAmount\Plugin\Quote\Model\Quote\Validator\MinimumOrderAmount
 */
class ValidationMessage
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $helper;

    /**
     * ValidationMessage constructor.
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Bss\OrderAmount\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Bss\OrderAmount\Helper\Data $helper
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Phrase|mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetMessage(
        \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage $subject,
        \Closure $proceed
    ) {
        $minimumAmount=$this->helper->getMinAmount();
        if (!$minimumAmount) {
            $minimumAmount = 0;
        }
        $minimumAmount = $this->pricingHelper->currency($minimumAmount, true, false);
        $message = $this->helper->getMessage();
        if (empty($message)) {
            $message = __('Minimum order amount is %1', $minimumAmount);
        } else {
            $message = str_replace("[min_amount]", $minimumAmount, $message);
            $message = __($message);
        }
        return $message;
    }
}
