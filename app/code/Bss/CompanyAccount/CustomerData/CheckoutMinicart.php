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

namespace Bss\CompanyAccount\CustomerData;

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;

/**
 * Class Checkout Mini cart
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class CheckoutMinicart implements SectionSourceInterface
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * CheckoutMinicart constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     * @param Session $customerSession
     * @param QuoteHelper $quoteHelper
     * @param Data $helper
     */
    public function __construct(
        PermissionsChecker $permissionsChecker,
        Session            $customerSession,
        QuoteHelper        $quoteHelper,
        Data               $helper
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->customerSession = $customerSession;
        $this->quoteHelper = $quoteHelper;
        $this->helper = $helper;
    }

    /**
     * Disable button Checkout mini-cart & check is approved quote
     *
     * @return bool[]|false[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSectionData()
    {
        if ($this->helper->isCompanyAccount()) {
            $output = [];
            ($subUser = $this->customerSession->getSubUser())
                ? $check = $this->quoteHelper->checkQuote(
                $subUser->getId(),
                SubUserQuoteInterface::SUB_USER_ID
            ) : $check = $this->quoteHelper->checkQuote(
                $this->customerSession->getCustomerId(),
                SubUserQuoteInterface::CUSTOMER_ID
            );
            ($check) ? $output['approved_quote'] = true : $output['approved_quote'] = false;
        }
        $output['check_order_role'] = !$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER);
        return $output;
    }
}
