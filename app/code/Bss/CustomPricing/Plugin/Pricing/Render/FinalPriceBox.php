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
namespace Bss\CustomPricing\Plugin\Pricing\Render;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class FinalPriceBox
 * @package Bss\CustomPricing\Plugin\Pricing\Render
 */
class FinalPriceBox
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    protected $helperRule;

    /**
     * FinalPriceBox constructor.
     * @param \Magento\Framework\App\Http\Context $context
     * @param \Bss\CustomPricing\Helper\Data $helperData
     * @param \Bss\CustomPricing\Helper\CustomerRule $helperRule
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $context,
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\CustomerRule $helperRule
    ) {
        $this->helperData = $helperData;
        $this->helperRule = $helperRule;
        $this->context = $context;
    }

    /**
     * Modify cache key price block
     *
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKey(
        $subject,
        $result
    ) {
        $isCustomerLoggedIn = $this->context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($this->helperData->isEnabled() && $isCustomerLoggedIn) {
            $ruleIds = $this->helperRule->getAppliedRules(null, false);
            if ($ruleIds) {
                $result = $result . $ruleIds;
            }
        }
        return $result;
    }
}
