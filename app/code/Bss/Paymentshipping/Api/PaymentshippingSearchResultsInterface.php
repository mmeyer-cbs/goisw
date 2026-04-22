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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface PaymentshippingSearchResultsInterface
 *
 * @package Bss\Paymentshipping\Api\Data
 */
interface PaymentshippingSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\Paymentshipping\Api\Data\PaymentshippingInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\Paymentshipping\Api\Data\PaymentshippingInterface[] $items
     * @return \Bss\Paymentshipping\Api\PaymentshippingSearchResultsInterface
     */
    public function setItems(array $items);
}
