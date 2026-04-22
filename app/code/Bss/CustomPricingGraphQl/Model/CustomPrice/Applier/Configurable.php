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

use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Configurable
 * Configurable product custom price applier
 */
class Configurable extends AbstractApplier
{

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(QuoteItem $item, int $customerGroupId, array $ruleIds)
    {
        if (!$ruleIds || !in_array($item->getProductType(), $this->getAllowedApplyProductTypes())) {
            return;
        }

        $product = $item->getProduct();

        // set current configurable product as child product to calculate final price
        if ($option = $item->getOptionByCode('simple_product')) {
            $product = $option->getProduct();
        }

        $this->process($item, $product, $customerGroupId, $ruleIds);
    }

    /**
     * Allowed apply for configurable product only
     *
     * @return array
     */
    public function getAllowedApplyProductTypes(): array
    {
        return [
            static::PRODUCT_CONFIGURABLE
        ];
    }
}
