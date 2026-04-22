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
namespace Bss\QuoteExtension\Observer\QuoteExtension;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Data\Customer;

/**
 * Class observer UpgradeCustomerEmail
 * Update request for quote customer email after corresponding customer email changed
 */
class UpgradeCustomerEmail implements ObserverInterface
{
    /**
     * @var \Bss\QuoteExtension\Model\Customer
     */
    protected $qECustomer;

    public function __construct(
        \Bss\QuoteExtension\Model\Customer $qECustomer
    ) {
        $this->qECustomer = $qECustomer;
    }

    /**
     * Upgrade request for quote customer email when customer has changed email
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Customer $originalCustomer */
        $originalCustomer = $observer->getEvent()->getOrigCustomerDataObject();
        if (!$originalCustomer) {
            return;
        }

        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        $customerEmail = $customer->getEmail();

        if ($customerEmail === $originalCustomer->getEmail()) {
            return;
        }
        $this->qECustomer->changeCustomerEmailQE($customer);

    }
}
