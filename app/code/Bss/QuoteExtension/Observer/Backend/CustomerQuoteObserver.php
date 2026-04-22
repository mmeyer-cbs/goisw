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
namespace Bss\QuoteExtension\Observer\Backend;

use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomerQuote
 */
class CustomerQuoteObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $quoteExtensionCollection;

    /**
     * @var \Bss\QuoteExtension\Model\QuoteCustomerGroupId
     */
    protected $quoteCustomerGroupId;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CollectionFactory $quoteExtensionCollection
     * @param \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory $quoteExtensionCollection,
        \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        $this->quoteCustomerGroupId = $quoteCustomerGroupId;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        try {
            $quote = $this->quoteRepository->getForCustomer($customer->getId() . "quote_extension");
            $this->quoteCustomerGroupId->saveQuoteCustomerGroupId($quote, $customer);
            if ($this->quoteCustomerGroupId->isEnableConfigSaveCustomer()) {
                $this->saveQuoteByQuoteExtension($customer);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Save all quote of quoteextension when customer change group: quote extension status pending,resubmit,updated
     *
     * @param CustomerInterface $customer
     */
    public function saveQuoteByQuoteExtension($customer)
    {
        $quoteExtensionCollection = $this->quoteExtensionCollection->create()
            ->addFieldToFilter("main_table.customer_id", $customer->getId())
            ->addFieldToFilter("main_table.status", ['in' => $this->quoteCustomerGroupId->listStatusAllow()]);
        foreach ($quoteExtensionCollection as $quoteExtension) {
            $this->quoteCustomerGroupId->saveQuoteObserver( $quoteExtension->getQuoteId(), $quoteExtension->getCustomerId(), $customer);
            $this->quoteCustomerGroupId->saveQuoteObserver($quoteExtension->getBackendQuoteId(), $quoteExtension->getCustomerId(), $customer);
            $this->quoteCustomerGroupId->saveQuoteObserver($quoteExtension->getTargetQuote(), $quoteExtension->getCustomerId(), $customer);
        }
    }
}
