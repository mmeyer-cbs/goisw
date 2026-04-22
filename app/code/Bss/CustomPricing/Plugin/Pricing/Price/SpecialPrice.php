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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Plugin\Pricing\Price;

/**
 * Class SpecialPrice
 *
 * @package Bss\CustomPricing\Plugin\Pricing\Price
 */
class SpecialPrice
{
    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    protected $helperRule;

    /**
     * SpecialPrice constructor.
     * @param \Bss\CustomPricing\Helper\Data $helperData
     * @param \Bss\CustomPricing\Helper\CustomerRule $helperRule
     */
    public function __construct(
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\CustomerRule $helperRule
    ) {
        $this->helperData = $helperData;
        $this->helperRule = $helperRule;
    }

    /**
     * Don't applied special price if config bss_custom_pricing/general/apply_normal_special_price = no
     *
     * @param \Magento\Catalog\Pricing\Price\SpecialPrice $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSpecialPrice(
        $subject,
        $result
    ) {
        $ruleIds = $this->helperRule->getAppliedRules(null, false);
        $isEnable = $this->helperData->isEnabled();
        if (!$ruleIds || !$isEnable) {
            return $result;
        }
        $isAppliedSpecialPrice = $this->helperData->applyNormalSpecialPrice();
        $productId = $subject->getProduct()->getId();
        $customerGroup = $this->helperRule->getCustomerSession()->getCustomer()->getGroupId();
        if (!$this->helperRule->getCustomerSession()->isLoggedIn()) {
            $customerGroup = 0;
        }
        $isAppliedCustomPrice = $this->helperRule->isAppliedCustomPrice($ruleIds, $productId, $customerGroup);
        if ($ruleIds && $isEnable && !$isAppliedSpecialPrice && $isAppliedCustomPrice) {
            return null;
        }
        return $result;
    }
}
