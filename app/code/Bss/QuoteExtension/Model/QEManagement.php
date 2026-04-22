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
 * @package    Bss_QuoteExtensionGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class for managing quote
 */
class QEManagement extends \Magento\Quote\Model\QuoteManagement implements \Bss\QuoteExtension\Api\QEManagementInterface
{
    /**
     * CreateEmptyQuote
     *
     * @param int $quoteId
     */
    public function createEmptyQuote($quoteId)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $quote = $this->createAnonymousQuote($storeId, $quoteId);

        $quote->setBillingAddress($this->quoteAddressFactory->create());
        $quote->setShippingAddress($this->quoteAddressFactory->create());
        $quote->setCustomerIsGuest(1);

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The quote can't be created."));
        }
        return $quote->getId();
    }

    /**
     * createEmptyQuoteForCustomer
     *
     * @param int $customerId
     * @param int|null $quoteId
     * @return mixed|string[]
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createEmptyQuoteForCustomer($customerId, $cartId = null)
    {
        if (!$cartId) {
            try {
                $cart = $this->getCartForCustomer($customerId);
                $cartId = $cart->getId();
            } catch (\Exception $exception) {
                $cartId = $this->createEmptyCartForCustomer($customerId);
            }

        }
        $storeId = $this->storeManager->getStore()->getStoreId();
        $quote = $this->createCustomerQuote($customerId, $storeId, $cartId);

        $this->_prepareCustomerQuote($quote);

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The quote can't be created."));
        }
        return [
            "data" => [
                "cart_id" => $cartId,
                "quote_id" => $quote->getId(),
            ]
        ];
    }

    /**
     * Creates an anonymous cart.
     *
     * @param int $storeId
     * @param int $quoteId
     * @return \Magento\Quote\Model\Quote Cart object.
     */
    protected function createAnonymousQuote($storeId, $quoteId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setQuoteExtension($quoteId);
        return $quote;
    }

    /**
     * Creates a cart for the currently logged-in customer.
     *
     * @param int $customerId
     * @param int $storeId
     * @param int $quoteId
     * @return \Magento\Quote\Model\Quote Cart object.
     * @throws CouldNotSaveException The cart could not be created.
     */
    protected function createCustomerQuote($customerId, $storeId, $quoteId)
    {
        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId . "quote_extension");
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customer = $this->customerRepository->getById($customerId);
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteFactory->create();
            $quote->setStoreId($storeId);
            $quote->setCustomer($customer);
            $quote->setCustomerIsGuest(0);
            $quote->setQuoteExtension($quoteId);
        }
        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function getQuoteForCustomer($customerId)
    {
        return $this->quoteRepository->getActiveForCustomer($customerId . "quote_extension");
    }

}
