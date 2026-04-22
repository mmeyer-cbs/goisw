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
 * Class PriceStrategy
 *
 * The Price Strategy source value
 */
class PriceStrategy implements \Magento\Framework\Option\ArrayInterface
{
    const MINIMUM_PRICE = 1;
    const PRIORITY = 2;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Minimum Price'),
                'value' => self::MINIMUM_PRICE
            ],
            [
                'label' => __('Priority'),
                'value' => self::PRIORITY
            ]
        ];
    }
}
