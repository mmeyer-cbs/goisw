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
 * Class TierPriceValue
 *
 * @package Bss\CustomPricing\Plugin\Pricing\Price
 */
class TierPriceValue
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
     * @param \Magento\Catalog\Pricing\Price\TierPrice $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(
        $subject,
        $result
    ) {
        $productId = $subject->getProduct()->getId();
        if (!$this->isCalculatorTierPrice($productId)) {
            return false;
        }
        return $result;
    }

    /**
     * Return [] if config bss_custom_pricing/general/apply_normal_tier_price = No
     *
     * @return array
     */
    public function afterGetTierPriceList(
        $subject,
        $result
    ) {
        $productId = $subject->getProduct()->getId();
        if (!$this->isCalculatorTierPrice($productId)) {
            return [];
        }
        return $result;
    }

    /**
     * Is calculator tier price to final price
     *
     * @param int $productId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCalculatorTierPrice($productId)
    {
        $ruleIds = $this->helperRule->getAppliedRules(null, false);
        $isEnable = $this->helperData->isEnabled();
        if (!$ruleIds || !$isEnable) {
            return true;
        }
        $isAppliedTierPrice = $this->helperData->applyNormalTierPrice();
        $customerGroup = $this->helperRule->getCustomerSession()->getCustomer()->getGroupId();
        if (!$this->helperRule->getCustomerSession()->isLoggedIn()) {
            $customerGroup = 0;
        }
        $isAppliedCustomPrice = $this->helperRule->isAppliedCustomPrice($ruleIds, $productId, $customerGroup);
        if ($ruleIds && $isEnable && !$isAppliedTierPrice && $isAppliedCustomPrice) {
            return false;
        }
        return true;
    }
}
