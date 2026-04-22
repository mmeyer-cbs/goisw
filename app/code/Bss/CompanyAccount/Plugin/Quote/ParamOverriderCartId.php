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

namespace Bss\CompanyAccount\Plugin\Quote;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ParamOverriderCartId
 *
 * @package Bss\CompanyAccount\Plugin\Quote
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ParamOverriderCartId
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ParamOverriderCartId constructor.
     *
     * @param SubUserRepositoryInterface $subUserRepository
     * @param Data $helper
     * @param UserContextInterface $userContext
     * @param CartManagementInterface $cartManagement
     * @param RequestInterface $request
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubUserRepositoryInterface $subUserRepository,
        Data                       $helper,
        UserContextInterface       $userContext,
        CartManagementInterface    $cartManagement,
        RequestInterface           $request,
        CheckoutSession            $checkoutSession,
        LoggerInterface            $logger
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->subUserRepository = $subUserRepository;
        $this->userContext = $userContext;
        $this->cartManagement = $cartManagement;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * { @inheritDoc }
     */
    public function getOverriddenValue()
    {
        try {
            if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerId = $this->userContext->getUserId();
                if ($this->checkoutSession->getCheckoutIsQuoteExtension()) {
                    return $this->checkoutSession->getCheckoutIsQuoteExtension();
                }

                $referer = $this->request->getHeader('Referer') ?? '';
                if (strpos($referer, 'quoteextension') !== false && $this->checkoutSession->getIsQuoteExtension()) {
                    return $this->checkoutSession->getIsQuoteExtension();
                }

                $urlHttpReferer = $this->request->getServer('HTTP_REFERER');
                if ($urlHttpReferer && (strpos($urlHttpReferer, "companyaccount/order/checkout/order_id/"))
                    || strpos($urlHttpReferer, "companyaccount=1")) {
                    return $this->checkoutSession->getQuoteId();
                }

                /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
                if ($subUser = $this->customerSession->getSubUser()) {
                    $cart = $this->subUserRepository->getQuoteBySubUser($subUser);
                    if ($cart) {
                        return $cart->getId();
                    }
                }

                /** @var \Magento\Quote\Api\Data\CartInterface */
                $cart = $this->cartManagement->getCartForCustomer($customerId);
                if ($cart) {
                    return $cart->getId();
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(__('Current customer does not have an active cart.'));
        }
        return null;
    }
}
