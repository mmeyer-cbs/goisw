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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Observer\Checkout;

use Magento\Framework\Event\Observer;

/**
 * Class Multishipping
 *
 */
class Multishipping implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        $order->getBillingAddress()
            ->setCustomerAddressAttribute($quote->getBillingAddress()->getCustomerAddressAttribute());
        $order->getShippingAddress()
            ->setCustomerAddressAttribute($quote->getShippingAddress()->getCustomerAddressAttribute());
        return $this;
    }
}
