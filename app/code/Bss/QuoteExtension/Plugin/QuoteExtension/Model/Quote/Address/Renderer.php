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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Plugin\QuoteExtension\Model\Quote\Address;

/**
 * Class Render
 */
class Renderer
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrderAddress
     */
    protected $quoteAddressToOrderAddress;

    public function __construct(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
    ) {
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
    }

    /**
     * Format address in a specific way
     *
     * @param $subject
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param string $type
     * @return array
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function beforeFormatQuoteAddress($subject, $address, $type)
    {
        $address = $this->quoteAddressToOrderAddress->convert($address);
        return [$address, $type];
    }

}
