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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricingGraphQl\Model\CustomPrice;

use Bss\CustomPricing\Helper\Data;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class BundlePrice
 * Calculate bundle selection final price
 */
class BundlePrice extends \Magento\Bundle\Model\Product\Price
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * BundlePrice constructor.
     *
     * @param Data $moduleHelperData
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     */
    public function __construct(
        Data $moduleHelperData,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null
    ) {
        $this->helperData = $moduleHelperData;
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
            $catalogData,
            $serializer,
            $tierPriceExtensionFactory
        );
    }

    /**
     * Get bundle selection price compare with custom pricing config
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float $bundleQty
     * @param float $selectionQty
     * @param bool $multiplyQty
     * @param bool $takeTierPrice
     * @return float|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelectionFinalTotalPrice(
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        // CORE CODE START
        if (null === $bundleQty) {
            $bundleQty = 1.;
        }
        if ($selectionQty === null) {
            $selectionQty = $selectionProduct->getSelectionQty();
        }

        if ($bundleProduct->getPriceType() == self::PRICE_TYPE_DYNAMIC) {
            $totalQty = $bundleQty * $selectionQty;
            if (!$takeTierPrice || $totalQty === 0) {
                $totalQty = 1;
            }
            $price = $selectionProduct->getFinalPrice($totalQty);
        } else {
            if ($selectionProduct->getSelectionPriceType()) {
                // percent
                $product = clone $bundleProduct;
                $product->setFinalPrice($this->getPrice($product));
                $this->_eventManager->dispatch(
                    'catalog_product_get_final_price',
                    ['product' => $product, 'qty' => $bundleQty]
                );
                $price = $product->getData('final_price') * ($selectionProduct->getSelectionPriceValue() / 100);
            } else {
                // fixed
                $price = $selectionProduct->getSelectionPriceValue();
            }
        }

        if ($multiplyQty) {
            $price *= $selectionQty;
        }
        // CORE CODE END

        $isAppliedTierPrice = $this->helperData->applyNormalTierPrice();
        $isAppliedSpecialPrice = $this->helperData->applyNormalSpecialPrice();

        $prices = [$price];

        if ($isAppliedTierPrice) {
            $prices[] = $this->_applyTierPrice($bundleProduct, $bundleQty, $price);
        }

        if ($isAppliedSpecialPrice) {
            $prices[] = $this->_applySpecialPrice($bundleProduct, $price);
        }

        return min(...$prices);
    }
}
