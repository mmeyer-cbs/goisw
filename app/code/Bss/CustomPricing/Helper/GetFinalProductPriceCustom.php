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

namespace Bss\CustomPricing\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class GetFinalProductPriceCustom
 * Get final price of product with cusotm pricing config
 */
class GetFinalProductPriceCustom extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice
     */
    protected $indexPrice;

    /**
     * GetFinalProductPriceCustom constructor.
     *
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param Data $helperData
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        Data $helperData,
        \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice $baseFinalPrice,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null
    ) {
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $tierPriceExtensionFactory
        );
        $this->helperData = $helperData;
        $this->indexPrice = $baseFinalPrice;
    }

    /**
     * Return final price custom
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param ProductInterface $product
     * @param float $priceRule
     * @param float $qty
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFinalPriceCustom($subject, $product, $priceRule, $qty)
    {
        $isAppliedTierPrice = $this->helperData->applyNormalTierPrice();
        $isAppliedSpecialPrice = $this->helperData->applyNormalSpecialPrice();
        if ($isAppliedSpecialPrice && $isAppliedTierPrice) {
            return min(
                $subject->_applyTierPrice($product, $qty, $priceRule),
                $subject->_applySpecialPrice($product, $priceRule)
            );
        }

        if ($isAppliedTierPrice && !$isAppliedSpecialPrice) {
            return $subject->_applyTierPrice($product, $qty, $priceRule);
        }

        if (!$isAppliedTierPrice && $isAppliedSpecialPrice) {
            return $subject->_applySpecialPrice($product, $priceRule);
        }

        return $priceRule;
    }

    /**
     * Get prices info from ruleids
     *
     * @param string|array $ruleIds
     * @param int $productId
     * @param int $customerGroup
     * @return false|mixed
     */
    public function getInfoPrices($ruleIds, $productId, $customerGroup)
    {
        $inforPrices = $this->indexPrice->getPriceFromIndex($ruleIds, $productId, $customerGroup);
        if ($inforPrices && is_array($inforPrices)) {
            $price = [];
            foreach ($inforPrices as $inforPrice) {
                $price[] = $inforPrice['price'];
            }
            return min($price);
        }
        return false;
    }
}
