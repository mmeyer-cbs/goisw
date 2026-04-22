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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Totals;

/**
 * Class Shipping
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\Quote\View\Totals
 */
class Shipping extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\Shipping
{
    /**
     * Retrieve formatted price
     *
     * @param float $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function formatPrice($value)
    {
        return $this->getLayout()->getBlock('totals')->formatPrice($value);
    }
}
