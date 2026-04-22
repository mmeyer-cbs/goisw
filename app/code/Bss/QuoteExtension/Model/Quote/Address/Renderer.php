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
namespace Bss\QuoteExtension\Model\Quote\Address;

/**
 * Class Renderer used for formatting the quote address
 *
 * @package Bss\QuoteExtension\Model\Quote\Address
 */
class Renderer extends \Magento\Sales\Model\Order\Address\Renderer
{

    /**
     * Format address in a specific way
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param string $type
     * @return string|null
     */
    public function formatQuoteAddress($address, $type)
    {
        return $this->format($address, $type);
    }
}
