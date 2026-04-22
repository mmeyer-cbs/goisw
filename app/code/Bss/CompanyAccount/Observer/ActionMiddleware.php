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
namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Event\Observer;

/**
 * Class DisableCheckout
 *
 * @package Bss\CompanyAccount\Observer
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ActionMiddleware implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * DisableCheckout constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param SubUserRepositoryInterface $subUserRepository
     * @param PermissionsChecker $permissionsChecker
     * @param Data $helper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        SubUserRepositoryInterface $subUserRepository,
        PermissionsChecker $permissionsChecker,
        Data $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->cart = $cart;
        $this->permissionsChecker = $permissionsChecker;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $this->loggedInSubUser();
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $routeName = $observer->getEvent()->getRequest()->getRouteName();
        $controller = $observer->getControllerAction();
        if ($routeName == 'multishipping'
            || $actionName == 'checkout_cart_index'
        ) {
            $this->checkoutSession->setDisableCheckout(false);
            if ($this->helper->isEnable() &&
                $this->customerSession->getSubUser() &&
                $this->customerSession->isLoggedIn()
            ) {
                if ($this->permissionsChecker->isAdmin()) {
                    return $this;
                }

                $orderAmount = $this->cart->getQuote()->getBaseSubtotal();
                $cantAccessWithOrderAmount = $this->permissionsChecker
                    ->isDenied(Permissions::MAX_ORDER_AMOUNT, $orderAmount);
                $cantAccessWithOrderPerDay = $this->permissionsChecker
                    ->isDenied(Permissions::MAX_ORDER_PERDAY);
                if ($cantAccessWithOrderAmount['is_denied'] ||
                    $cantAccessWithOrderPerDay['is_denied']
                ) {
                    $this->checkoutSession->setDisableCheckout(true);
                    if ($actionName != 'checkout_cart_index') {
                        $this->helper->getRedirect()->redirect($controller->getResponse(), 'checkout/cart/index');
                    } else {
                        if ($cantAccessWithOrderAmount['is_denied']) {
                            $this->permissionsChecker->getMessageManager()->addErrorMessage(
                                __(
                                    'You just can checkout cart with amount less than %1',
                                    [
                                        $cantAccessWithOrderAmount['accessible_value']
                                    ]
                                )
                            );
                        }

                        if ($cantAccessWithOrderPerDay['is_denied']) {
                            $this->permissionsChecker->getMessageManager()->addErrorMessage(
                                __(
                                    'You have reached the maximum (%1) number of order perday.',
                                    [
                                        $cantAccessWithOrderPerDay['accessible_value']
                                    ]
                                )
                            );
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Clear session data
     *
     * If module is disabled or sub-user account was disabled
     * Will clear session data and return to login page
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loggedInSubUser()
    {
        if (!$this->customerSession->getSubUser()) {
            return $this;
        }
        $backLogin = false;
        $message = '';
        if ($this->customerSession->isLoggedIn()) {
            if (!$this->helper->isEnable()) {
                $message .= __('The company account configuration on website was disabled.');
                $backLogin = true;
            }

            /** @var SubUserInterface $subUser */
            if ($subUser = $this->customerSession->getSubUser()) {
                $subUser = $this->subUserRepository->getById($subUser->getSubId());
                if (!$subUser->getSubStatus()) {
                    $message .= __('Your account was disabled.');
                    $backLogin = true;
                }
            }
        }

        $customerId = $this->customerSession->getId();
        if ($backLogin && $customerId) {
            $this->customerSession->logout()
                ->setBeforeAuthUrl($this->helper->getRedirect()->getRefererUrl())
                ->setLastCustomerId($customerId);
            $this->customerSession->clearStorage();
            $this->helper->getMessageManager()->addErrorMessage(
                __('The current session has expired.') . ' ' . $message . ' ' .
                __('Please reload to update page.')
            );
        }

        return $this;
    }
}
