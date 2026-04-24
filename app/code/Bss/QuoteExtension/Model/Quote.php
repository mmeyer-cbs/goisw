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
namespace Bss\QuoteExtension\Model;

/**
 * Class Quote
 */
class Quote extends \Magento\Quote\Model\Quote
{
    /**
     * Get Request quote by quote id
     *
     * @param int $quoteId
     * @return \Magento\Framework\DataObject
     */
    public function getQuoteExtenstion($quoteId)
    {
        return $this->getCollection()
            ->addFieldToFilter('quote_extension', $quoteId)
            ->addFieldToFilter('is_active', 1)
            ->getLastItem();
    }

    /**
     * Get Request quote by quote id
     *
     * @param int $customerId
     * @return \Magento\Framework\DataObject
     */
    public function getCustomerQuoteExtenstion($customerId)
    {
        return $this->getCollection()
            ->addFieldToFilter('quote_extension', ['neq' => 'NULL'])
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_active', 1)
            ->getLastItem();
    }

    /**
     * Loading quote data by customer
     *
     * @param int|\Magento\Customer\Model\Customer $customer
     * @return $this|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($customer)
    {
        /* @TODO: remove this if after external usages of loadByCustomerId are refactored in MAGETWO-19935 */
        if ($customer && strpos($customer, 'quote_extension') !== false) {
            $customerId = (int)str_replace('quote_extension', '', $customer);
            $this->_getResource()->loadQuoteExtensionForByCustomer($this, $customerId);
            $this->_afterLoad();
            return $this;
        } else {
            return parent::loadByCustomer($customer);
        }
    }

    public function cloneQuoteExtension(&$quote)
    {
        $data = $this->getData();
        if (isset($data['entity_id'])) {
            unset($data['entity_id']);
        }
        $quote->setData($data);
        $quote->merge($this);
        if ($this->getBillingAddress()->getId()) {
            $data = $this->getBillingAddress()->getData();
            unset($data['address_id']);
            $quote->getBillingAddress()->addData($data);
        }

        if ($this->getShippingAddress()->getId()) {
            $data = $this->getShippingAddress()->getData();
            unset($data['address_id']);
            $quote->getShippingAddress()
                ->addData($data)
                ->setCollectShippingRates(true)
                ->collectShippingRates();
        }
        return $this;
    }
}
