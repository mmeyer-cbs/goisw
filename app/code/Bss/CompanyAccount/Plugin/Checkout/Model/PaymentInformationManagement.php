<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Plugin\Checkout\Model;

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Bss\CompanyAccount\Helper\Data;

/**
 * Class PaymentInformationManagement
 *
 * @package Bss\CompanyAccount\Plugin\Checkout
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PaymentInformationManagement
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Function construct
     *
     * @param QuoteHelper $quoteHelper
     * @param Data $helper
     */
    public function __construct(
        QuoteHelper             $quoteHelper,
        Data                    $helper
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->helper = $helper;
    }

    /**
     * Set quote id to save payment information and place order
     *
     * @param $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array|null
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteId = $this->getCurrentQuoteId();
        if ($quoteId) {
            return [$quoteId, $paymentMethod, $billingAddress];
        }
        return null;
    }

    /**
     * Set quote id to save payment information
     *
     * @param $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array|null
     */
    public function beforeSavePaymentInformation(
        $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteId = $this->getCurrentQuoteId();
        if ($quoteId) {
            return [$quoteId, $paymentMethod, $billingAddress];
        }
        return null;
    }

    /**
     * Function set quote id to get payment information
     *
     * @param $subject
     * @param $cartId
     * @return array|null
     */
    public function beforeGetPaymentInformation($subject, $cartId)
    {
        $quoteId = $this->getCurrentQuoteId();
        if ($quoteId) {
            return [$quoteId];
        }
        return [$cartId];
    }

    /**
     * Get current quote id
     *
     * @return bool|int
     */
    private function getCurrentQuoteId()
    {
        if ($this->helper->isCompanyAccount()) {
            $customerId = $this->quoteHelper->getCustomerSession()->getCustomerId();
            ($subUser = $this->quoteHelper->getCustomerSession()->getSubUser())
                ? $quoteId = $this->quoteHelper->checkQuote($subUser->getId(), SubUserQuoteInterface::SUB_USER_ID)
                : $quoteId = $this->quoteHelper->checkQuote($customerId, SubUserQuoteInterface::CUSTOMER_ID);
            return $quoteId;
        } else {
            return false;
        }
    }
}
