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
use Magento\Checkout\Api\Data\ShippingInformationInterface;

/**
 * Class ShippingInformationManagement
 *
 * @package Bss\CompanyAccount\Plugin\Checkout
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ShippingInformationManagement
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
        QuoteHelper $quoteHelper,
        Data        $helper
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->helper = $helper;
    }

    /**
     * Set quote id to save address information
     *
     * @param $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array|null
     */
    public function beforeSaveAddressInformation($subject, $cartId, ShippingInformationInterface $addressInformation)
    {
        if ($this->helper->isCompanyAccount()) {
            $customerId = $this->quoteHelper->getCustomerSession()->getCustomerId();
            ($subUser = $this->quoteHelper->getCustomerSession()->getSubUser())
                ? $quoteId = $this->quoteHelper->checkQuote($subUser->getId(), SubUserQuoteInterface::SUB_USER_ID)
                : $quoteId = $this->quoteHelper->checkQuote($customerId, SubUserQuoteInterface::CUSTOMER_ID);
            if ($quoteId) {
                return [$quoteId, $addressInformation];
            }
        }
        return [$cartId, $addressInformation];
    }
}
