<?php
declare(strict_types=1);
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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Observer;

use Magento\Framework\Event\Observer;

/**
 * Class SetCustomPricingRuleObserver
 * Set customer custom pricing applied rules to session for disable page_cache
 */
class SetCustomPricingRuleObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    protected $helperRule;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * SetCustomPricingRuleObserver constructor.
     *
     * @param \Bss\CustomPricing\Helper\CustomerRule $helperRule
     * @param \Bss\CustomPricing\Helper\Data $helperData
     */
    public function __construct(
        \Bss\CustomPricing\Helper\CustomerRule $helperRule,
        \Bss\CustomPricing\Helper\Data $helperData
    ) {
        $this->helperRule = $helperRule;
        $this->helperData = $helperData;
    }

    /**
     * Set customer custom pricing applied rules to session for disable page_cache
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        // Check if module disable or current applied rules were be set to set to session
        //// -> SKIP
        if (!$this->helperData->isEnabled() || $this->helperRule->getAppliedRules()) {
            return;
        }

        $this->helperRule->setAppliedRules();
    }
}
