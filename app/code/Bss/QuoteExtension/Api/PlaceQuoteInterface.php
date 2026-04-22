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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api;

/**
 * @api
 */
interface PlaceQuoteInterface
{
    /**
     * Set shipping information and place quote for a specified quote cart.
     *
     * @param int $cartId
     * @param string|null $customerNote
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface|null $shippingMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $shippingAddress
     * @param mixed|null $additionalData
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface|string|null $shippingMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $shippingAddress
     * @param array|null $additional
     * @return int
     */
    public function saveShippingInformationAndPlaceQuote(
        $cartId,
        $customerNote = null,
        $shippingMethod = null,
        $shippingAddress = null,
        $additional = null
    );
}
