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
namespace Bss\QuoteExtension\Model;

use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class Customer
 */
class Customer
{
    const PATH_SAVE_CUSTOMER = 'bss_request4quote/general/save_customer';

    /**
     * @var array
     */
    protected $customer = [];

    /**
     * @var CollectionFactory
     */
    protected $qECollection;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory $qECollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->qECollection = $qECollection;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Reset customer id if customer deleted
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $quoteExtension
     * @return void
     */
    public function resetCustomerId($quoteExtension)
    {
        if ($customerId = $quoteExtension->getCustomerId()) {
            try {
                $this->customerRepository->getById($customerId);
            } catch (\Exception $exception) {
                $quoteExtension->setCustomerId(null);
                try {
                    $quoteExtension->save();
                } catch (\Exception $exception) {
                }
            }
        }
    }

    /**
     * Change customer email when customer changed email
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     */
    public function changeCustomerEmail($quote)
    {
        if ($customerId = $quote->getCustomerId()) {
            try {
                $customer = $this->getCustomerById($customerId);
                if ($customer && $customer->getEmail() != $quote->getCustomerEmail()) {
                    $quote->setCustomerEmail($customer->getEmail());
                    $quote->save();
                }
            } catch (\Exception $exception) {
            }
        }
    }

    /**
     * Check and chage customer email quote extension
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $quoteExtension
     */
    public function checkChangeCustomerEmailQE($quoteExtension)
    {
        if ($customerId = $quoteExtension->getCustomerId()) {
            $customer = $this->getCustomerById($customerId);
            if ($customer->getEmail() != $quoteExtension->getEmail()) {
                $this->changeCustomerEmailQE($customer);
            }
        }
    }

    /**
     * Change customer email QuoteExtension
     *
     * @param CustomerInterface $customer
     */
    public function changeCustomerEmailQE($customer)
    {
        try {
            $qECollection = $this->qECollection->create()->addFieldToFilter("main_table.customer_id", $customer->getId());
            foreach ($qECollection as $qe) {
                $qe->setEmail($customer->getEmail());
            }
            $qECollection->save();
        } catch (\Exception $exception) {
        }
    }

    /**
     * Get customer name
     *
     * @param CustomerInterface $customer
     * @return string
     */
    public function getCustomerName($customer)
    {
        $customerName = '';
        if ($customer->getPrefix()) {
            $customerName .= $customer->getPrefix() . ' ';
        }
        $customerName .= $customer->getFirstname();
        if ($customer->getMiddlename()) {
            $customerName .= ' ' . $customer->getMiddlename();
        }
        $customerName .= ' ' . $customer->getLastname();
        if ($customer->getSuffix()) {
            $customerName .= ' ' . $customer->getSuffix();
        }
        return $customerName;
    }

    /**
     * Get customer by id
     *
     * @param int $customerId
     * @return null|CustomerInterface
     */
    public function getCustomerById($customerId)
    {
        if (!isset($this->customer[$customerId])) {
            try {
                $this->customer[$customerId] = $this->customerRepository->getById($customerId);
            } catch (\Exception $exception) {
                $this->customer[$customerId] = null;
            }
        }
        return $this->customer[$customerId];
    }

    /**
     * Get customer name by id
     *
     * @param int $customerId
     * @return mixed|string|null
     */
    public function getCustomerNameById($customerId)
    {
        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                return $this->getCustomerName($customer);
            } catch (\Exception $exception) {
                return null;
            }
        }
        return $customerId;
    }
}
