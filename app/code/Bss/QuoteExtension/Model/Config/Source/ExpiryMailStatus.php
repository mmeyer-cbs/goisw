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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ExpiryMailStatus
 *
 * @package Bss\QuoteExtension\Model\Config\Source
 */
class ExpiryMailStatus implements OptionSourceInterface
{
    const STATUS_SENT = 1;
    const STATUS_NOT_SENT = 0;

    /**
     * Get Grid row type array for option element.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::STATUS_SENT => __('Sent'),
            self::STATUS_NOT_SENT => __('Not Sent'),
        ];
    }
}
