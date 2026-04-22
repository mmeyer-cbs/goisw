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
use Bss\CustomPricingGraphQl\Model\CustomPrice\BundlePrice;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Bundle
 * Bundle product custom price applier
 */
class Bundle extends AbstractApplier
{
    /**
     * Dynamic bundle price type
     */
    const PRICE_TYPE_DYNAMIC = 0;

    /**
     * @var BundlePrice
     */
    protected $bundlePriceCustom;

    /**
     * Bundle constructor.
     *
     * @param GetFinalProductPriceCustom $getFinalProductPriceCustom
     * @param BundlePrice $bundlePriceCustom
     */
    public function __construct(
        GetFinalProductPriceCustom $getFinalProductPriceCustom,
        BundlePrice $bundlePriceCustom
    ) {
        parent::__construct($getFinalProductPriceCustom);
        $this->bundlePriceCustom = $bundlePriceCustom;
    }

    /**
     * @inheritDoc
     */
    public function apply(QuoteItem $item, int $customerGroupId, array $ruleIds)
    {
        if (!$ruleIds) {
            return;
        }

        $product = $item->getProduct();

        // Set custom price for bundle not dynamic price (just for parent, selection not available)
        if ($product->getTypeId() === "bundle" &&
            $product->getPriceType() != self::PRICE_TYPE_DYNAMIC
        ) {

            $totalBundleItemsPrice = $item->getProduct()->getPriceModel()
                ->getTotalBundleItemsPrice($product, $item->getQty());

            $totalPrice = $item->getCustomPrice() + $totalBundleItemsPrice;
            // Set non-dynamic price for bundle with all default price of items
            $this->setFinalCustomPrice($item, (float) $totalPrice);

            return;
        }

        // Skip bundle dynamic price
        if ($product->getTypeId() === "bundle" &&
            $product->getPriceType() == self::PRICE_TYPE_DYNAMIC
        ) {
            return;
        }

        $bundleProductItem = $item->getParentItem();

        if (!$bundleProductItem) {
            return;
        }

        $bundleProduct = $bundleProductItem->getProduct();

        // Skip if parent is not bundle product
        if ($bundleProduct->getTypeId() !== "bundle") {
            return;
        }

        $bundleQty = $bundleProductItem->getQty();
        // get custom price of bundle child product in bss index table
        $customPrice = $this->getCustomPrice($ruleIds, (int) $item->getProduct()->getId(), $customerGroupId);

        // Get final price of product include tier, special
        $customPrice = $this->getFinalProductPriceCustom->getFinalPriceCustom(
            $item->getProduct()->getPriceModel(),
            $item->getProduct(),
            $customPrice,
            $item->getQty()
        );

        // get selection final price of bundle child (include apply special price and tier price)
        $customPrice = $this->bundlePriceCustom->getSelectionFinalTotalPrice(
            $bundleProduct,
            $item->getProduct()->setPrice($customPrice),
            $bundleQty,
            $item->getQty(),
            false
        );

        $this->setFinalCustomPrice($item, $customPrice);
    }
}
