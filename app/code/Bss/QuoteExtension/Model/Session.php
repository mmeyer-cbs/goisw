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
namespace Bss\QuoteExtension\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Session
 *
 * @package Bss\QuoteExtension\Model
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Session extends \Magento\Checkout\Model\Session
{
    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $quoteExtension;

    /**
     * Get checkout quote instance by current session
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getQuoteExtension()
    {
        if ($this->quoteExtension === null) {
            $quoteExtension = $this->getQuote();
            if (!$quoteExtension->getId()) {
                $quoteExtension = $this->quoteFactory->create();
                $quoteExtension->setStore($this->_storeManager->getStore());
                if ($customerId = $this->_customerSession->getCustomerId()) {
                    $quoteExtension->setCustomer($this->customerRepository->getById($customerId));
                }
                $quoteExtension->save();
                $this->setQuoteId($quoteExtension->getId());
            }

            $quoteId = $quoteExtension->getId();
            if ($customerId = $this->_customerSession->getCustomerId()) {
                $quote = $quoteExtension->getCustomerQuoteExtenstion($customerId);
            } else {
                $quote = $quoteExtension->getQuoteExtenstion($quoteId);
            }

            if (!$quote->getId()) {
                if ($this->_customerSession->isLoggedIn()) {
                    try {
                        $quote = $this->quoteRepository->getActiveForCustomer(
                            $this->_customerSession->getCustomerId() . "quote_extension"
                        );
                        $this->setQuoteExtensionId($quote->getId());
                        $quote->setCustomer(
                            $this->customerRepository->getById($this->_customerSession->getCustomerId())
                        );
                        $quote->setStore($this->_storeManager->getStore());
                        $quote->setData('quote_extension', $quoteId);
                        $quote->save();
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    }
                }
                if (!$quote->getId()) {
                    $quote = $this->quoteFactory->create();
                    $quote->setStore($this->_storeManager->getStore());
                    if ($customerId = $this->_customerSession->getCustomerId()) {
                        $quote->setCustomer($this->customerRepository->getById($customerId));
                    }
                    $quote->setData('quote_extension', $quoteId);
                    $quote->save();
                    $quote->setQuoteExtensionId($quote->getId());
                }
                $quote = $this->quoteRepository->get($quote->getId());
            }
            $this->quoteExtension = $quote;
        }

        return $this->quoteExtension;
    }

    /**
     * Load Customer Form Reqest Quote
     *
     * @return Session|\Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadCustomerQuoteExtension()
    {
        if (!$this->_customerSession->getCustomerId()) {
            return $this;
        }
        $this->_eventManager->dispatch(
            'load_customer_quote_extension_before',
            ['quote_session' => $this]
        );

        try {
            $customerQuote = $this->quoteRepository->getForCustomer(
                $this->_customerSession->getCustomerId() . "quote_extension"
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerQuote = $this->quoteFactory->create();
        }

        if ($customerQuote->getId()) {
            if ($customerQuote->getStoreId() != $this->_storeManager->getStore()->getId()) {
                $customerQuote->setStoreId($this->_storeManager->getStore()->getId());
                $this->quoteRepository->save($customerQuote);
            }
        } else {
            $customerQuote->setStoreId($this->_storeManager->getStore()->getId());
        }

        if ($customerQuote->getId() && $this->getQuoteExtensionId() != $customerQuote->getId()) {
            if ($this->getQuoteExtensionId()) {
                $quoteExtension = $this->quoteRepository->get($this->getQuoteExtensionId());
                $this->quoteRepository->save(
                    $customerQuote->merge($quoteExtension)->collectTotals()
                );
            }

            $this->setQuoteExtensionId($customerQuote->getId());

            if ($this->quoteExtension) {
                $this->quoteRepository->delete($this->quoteExtension);
            }
            $this->quoteExtension = $customerQuote;
        } else {
            $this->mergeQuoteCreateAccount();
            $this->getQuoteExtension()->getBillingAddress();
            $this->getQuoteExtension()->getShippingAddress();
            $this->getQuoteExtension()->setCustomer($this->_customerSession->getCustomerDataObject())
                ->setTotalsCollectedFlag(false)
                ->collectTotals();
            $this->quoteRepository->save($this->getQuoteExtension());
        }
        return $this->quoteExtension;
    }

    /**
     * Get Quote Extension Id Key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuoteExtensionIdKey()
    {
        return 'quote_extension_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * Set Quote Extension Id
     *
     * @param int $quoteId
     * @return void
     * @codeCoverageIgnore
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setQuoteExtensionId($quoteId)
    {
        $this->storage->setData($this->getQuoteExtensionIdKey(), $quoteId);
    }

    /**
     * Get Quote Extension Id
     *
     * @return int
     * @codeCoverageIgnore
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteExtensionId()
    {
        return $this->getData($this->getQuoteExtensionIdKey());
    }

    /**
     * Get Last Extension Id
     *
     * @return string
     * @codeCoverageIgnore
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getLastQuoteExtensionIdKey()
    {
        return 'last_quote_extension_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * Set Last Extension Id
     *
     * @param int $quoteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setLastQuoteExtensionId($quoteId)
    {
        $this->storage->setData($this->getLastQuoteExtensionIdKey(), $quoteId);
    }

    /**
     * Get Last Extension Id
     *
     * @return int
     * @codeCoverageIgnore
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLastQuoteExtensionId()
    {
        return $this->getData($this->getLastQuoteExtensionIdKey());
    }

    /**
     * Destroy/end a session
     *
     * Unset all data associated with object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function clearQuoteExtension()
    {
        $this->quoteExtension = null;
        $this->setQuoteExtensionId(null);
        $this->setLastQuoteExtensionId(null);
        return $this;
    }

    /**
     * Merge quote when create account
     *
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function mergeQuoteCreateAccount()
    {
        if ($this->getQuoteExtensionId()) {
            $quoteExtension = $this->getQuote();
            $customerId = $this->_customerSession->getCustomerId();
            $quote = $quoteExtension->getCustomerQuoteExtenstion($customerId);
            if (!$quote->getQuoteId()) {
                try {
                    $this->quoteExtension = $this->quoteRepository->get($this->getQuoteExtensionId());
                } catch (\Exception $exception) {
                }

            }
        }
    }
}
