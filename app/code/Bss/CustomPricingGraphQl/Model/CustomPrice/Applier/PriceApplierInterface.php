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
 * Interface PriceApplierInterface
 * Price applier for specific product type
 */
interface PriceApplierInterface
{
    /**
     * Apply custom price action
     *
     * @param QuoteItem $item
     * @param int $customerGroupId
     * @param array $ruleIds
     * @return void
     */
    public function apply(QuoteItem $item, int $customerGroupId, array $ruleIds);
}
