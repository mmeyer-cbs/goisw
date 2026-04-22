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

namespace Bss\CustomPricing\Model\Config\Source;

/**
 * Enable disable options class
 */
class PriceTypeOption implements \Magento\Framework\Option\ArrayInterface
{
    const ABSOLUTE_PRICE = 1;
    const INCREASE_FIXED_PRICE = 2;
    const DECREASE_FIXED_PRICE = 3;
    const INCREASE_PERCENT_PRICE = 4;
    const DECREASE_PERCENT_PRICE = 5;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                "label" => __("Absolute Price"),
                "value" => self::ABSOLUTE_PRICE
            ],
            [
                "label" => __("Increase Fixed"),
                "value" => self::INCREASE_FIXED_PRICE
            ],
            [
                "label" => __("Decrease Fixed"),
                "value" => self::DECREASE_FIXED_PRICE
            ],
            [
                "label" => __("Increase Percentage"),
                "value" => self::INCREASE_PERCENT_PRICE
            ],
            [
                "label" => __("Decrease Percentage"),
                "value" => self::DECREASE_PERCENT_PRICE
            ]
        ];
    }
}
