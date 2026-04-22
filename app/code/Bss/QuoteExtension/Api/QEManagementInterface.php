<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\QuoteExtension\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Interface CartManagementInterface
 * @api
 * @since 100.0.2
 */
interface QEManagementInterface
{
    /**
     * createEmptyQuoteForCustomer
     *
     * @param int $customerId
     * @param int|null $cartId
     * @return mixed|string[]
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createEmptyQuoteForCustomer($customerId, $cartId = null);

    /**
     * Returns information for the cart for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Quote\Api\Data\CartInterface Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     */
    public function getQuoteForCustomer($customerId);

}
