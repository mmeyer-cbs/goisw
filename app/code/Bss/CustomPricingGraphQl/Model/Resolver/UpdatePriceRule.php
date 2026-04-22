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
namespace Bss\CustomPricingGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Class UpdatePriceRule
 * Update the price rule
 */
class UpdatePriceRule extends CreatePriceRule implements ResolverInterface
{
    const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing_edit_rule";
}
