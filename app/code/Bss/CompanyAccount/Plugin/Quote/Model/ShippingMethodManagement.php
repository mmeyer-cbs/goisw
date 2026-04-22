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

namespace Bss\CompanyAccount\Plugin\Quote\Model;

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Magento\Quote\Model\ShippingMethodManagement as CoreShippingMethod;

/**
 * Class CartTotalRepository
 *
 * @package Bss\CompanyAccount\Plugin\Quote\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ShippingMethodManagement
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * Function construct
     *
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(QuoteHelper $quoteHelper)
    {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Set cart id to estimate
     *
     * @param CoreShippingMethod $subject
     * @param int|string $cartId
     * @param int|string $addressId
     * @return string[]
     */
    public function beforeEstimateByAddressId($subject, $cartId, $addressId)
    {
        $customerId = $this->quoteHelper->getCustomerSession()->getCustomerId();
        ($subUser = $this->quoteHelper->getCustomerSession()->getSubUser())
            ? $quoteId = $this->quoteHelper->checkQuote($subUser->getId(), SubUserQuoteInterface::SUB_USER_ID)
            : $quoteId = $this->quoteHelper->checkQuote($customerId, SubUserQuoteInterface::CUSTOMER_ID);
        if ($quoteId) {
            return [$quoteId, $addressId];
        };
        return [$cartId, $addressId];
    }
}
