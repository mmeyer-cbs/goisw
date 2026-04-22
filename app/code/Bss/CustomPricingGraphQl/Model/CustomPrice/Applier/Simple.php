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

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Simple applier
 * simple|downloadable|virtual quote item product custom price applier
 */
class Simple extends AbstractApplier
{
    /**
     * Apply custom price for simple|downloadable|virtual quote item product
     *
     * @param QuoteItem $item
     * @param int $customerGroupId
     * @param array $ruleIds
     * @throws LocalizedException
     */
    public function apply(QuoteItem $item, int $customerGroupId, array $ruleIds = [])
    {
        if (!$ruleIds || !in_array($item->getProductType(), $this->getAllowedApplyProductTypes())) {
            return;
        }

        // SKIP bundle child or configurable
        $parentItem = $item->getParentItem();
        if ($parentItem &&
            (
                $parentItem->getProductType() === static::PRODUCT_BUNDLE ||
                $parentItem->getProductType() == static::PRODUCT_CONFIGURABLE
            )
        ) {
            return;
        }

        $product = $item->getProduct();

        $this->process($item, $product, $customerGroupId, $ruleIds);
    }

    /**
     * Get allowed apply product types
     *
     * @return array
     */
    public function getAllowedApplyProductTypes(): array
    {
        return [
            static::PRODUCT_SIMPLE,
            static::PRODUCT_VIRTUAL,
            static::PRODUCT_DOWNLOADABLE
        ];
    }
}
