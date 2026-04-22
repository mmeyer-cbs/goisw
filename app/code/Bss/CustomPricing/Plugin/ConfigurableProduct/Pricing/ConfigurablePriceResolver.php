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
namespace Bss\CustomPricing\Plugin\ConfigurableProduct\Pricing;

use Magento\Store\Model\StoreManagerInterface;
use Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver\ConfigurablePrice;

/**
 * Class ConfigurablePriceResolver
 *
 * @package Bss\CustomPricing\Plugin\ConfigurableProduct\Pricing
 */
class ConfigurablePriceResolver
{
    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    private $helperData;

    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    private $helperRule;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreManagerInterface
     */
    private $configurablePrice;

    public function __construct(
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\CustomerRule $helperRule,
        StoreManagerInterface $storeManager,
        ConfigurablePrice $configurablePrice
    ) {
        $this->helperData = $helperData;
        $this->helperRule = $helperRule;
        $this->storeManager = $storeManager;
        $this->configurablePrice = $configurablePrice;
    }

    /**
     * Modify data if customer apply rule. customer_group, customer_logged_in, rule_id
     *
     * @param \Magento\Framework\App\Http\Context $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolvePrice(
        $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $product
    ) {
        $storeId = $product->getStoreId() ?: $this->storeManager->getStore()->getId();
        $parentProductId = $product->getId();
        if ($this->helperData->isEnabled()) {
            $ruleIds = $this->helperRule->getAppliedRules(null, false);
            if ($ruleIds) {
                $minCustomPriceRule = $this->configurablePrice->getMinPrice($ruleIds, $parentProductId, $storeId);
                if ($minCustomPriceRule) {
                    return $minCustomPriceRule;
                }
            }
        }

        return $result;
    }
}
