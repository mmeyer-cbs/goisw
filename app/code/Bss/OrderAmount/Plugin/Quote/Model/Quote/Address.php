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
namespace Bss\OrderAmount\Plugin\Quote\Model\Quote;

/**
 * Class Address
 *
 * @package Bss\OrderAmount\Plugin\Quote\Model\Quote
 */
class Address
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\OrderAmount\Helper\Data
     */
    protected $helper;

    /**
     * Address constructor.
     *
     * @param \Bss\OrderAmount\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Bss\OrderAmount\Helper\Data                       $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param \Closure $proceed
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidateMinimumAmount(
        \Magento\Quote\Model\Quote\Address $subject,
        \Closure                           $proceed
    ) {
        $storeId = $subject->getQuote()->getStoreId();
        $validateEnabled = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$validateEnabled) {
            return true;
        }

        if ((!$subject->getQuote()->getIsVirtual() xor
            $subject->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING)) {
            return true;
        }

        $minAmount=$this->helper->getMinAmount();
        if (!$minAmount) {
            return true;
        }

        $includeDiscount = $this->scopeConfig->getValue(
            'sales/minimum_order/include_discount_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $taxInclude = $this->scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxes = 0;
        if ($taxInclude) {
            $taxes = $subject->getBaseTaxAmount();
        }

        if ($includeDiscount) {
            return ($subject->getBaseSubtotalWithDiscount() + $taxes >= $minAmount);
        }

        return ($subject->getBaseSubtotal() + $taxes >= $minAmount);
    }
}
