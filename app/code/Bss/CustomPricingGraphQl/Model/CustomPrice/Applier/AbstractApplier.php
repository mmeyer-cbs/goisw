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

namespace Bss\CustomPricingGraphQl\Model\CustomPrice\Applier;

use Bss\CustomPricing\Helper\GetFinalProductPriceCustom;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class AbstractApplier
 * Applier abstract class
 */
abstract class AbstractApplier implements PriceApplierInterface
{
    const PRODUCT_BUNDLE = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
    const PRODUCT_SIMPLE = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
    const PRODUCT_VIRTUAL = \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;
    const PRODUCT_DOWNLOADABLE = \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    const PRODUCT_CONFIGURABLE = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;

    /**
     * @var GetFinalProductPriceCustom
     */
    protected $getFinalProductPriceCustom;

    /**
     * Simple constructor.
     *
     * @param GetFinalProductPriceCustom $getFinalProductPriceCustom
     */
    public function __construct(
        GetFinalProductPriceCustom $getFinalProductPriceCustom
    ) {
        $this->getFinalProductPriceCustom = $getFinalProductPriceCustom;
    }

    /**
     * @param QuoteItem $item
     * @param ProductInterface $product
     * @param int $customerGroupId
     * @param array $ruleIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function process(QuoteItem $item, ProductInterface $product,int $customerGroupId, array $ruleIds)
    {
        $productId = $product->getId();
        $price = $product->getPriceModel();
        $customPrice = $this->getCustomPrice($ruleIds, (int) $productId, $customerGroupId);

        if (!$customPrice) {
            return;
        }

        // set custom base price for product for next calculation step
        $product->setPrice($customPrice);
        $customPrice = $this->getFinalProductPriceCustom->getFinalPriceCustom(
            $price,
            $product,
            $customPrice,
            $item->getQty()
        );

        $this->setFinalCustomPrice($item, (float) $customPrice);
    }

    /**
     * Get final price of product in for customer group
     *
     * @param array $ruleIds
     * @param int $productId
     * @param int $customerGroupId
     * @return false|float
     */
    public function getCustomPrice(array $ruleIds, int $productId, int $customerGroupId)
    {
        return $this->getFinalProductPriceCustom->getInfoPrices($ruleIds, $productId, $customerGroupId);
    }

    /**
     * Set custom price for quote item
     *
     * @param QuoteItem $item
     * @param float $customPrice
     */
    protected function setFinalCustomPrice(QuoteItem $item, float $customPrice)
    {
        $item->setCustomPrice($customPrice);
        $item->setOriginalCustomPrice($customPrice);
        $item->getProduct()->setIsSuperMode(true);
    }
}
