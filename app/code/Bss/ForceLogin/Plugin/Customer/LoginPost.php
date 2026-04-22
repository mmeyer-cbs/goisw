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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Plugin\Customer;

use Bss\ForceLogin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class LoginPost
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * LoginPost constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Data $helperData
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        Data $helperData,
        Session $customerSession
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->registry = $registry;
    }

    /**
     * Redirect customer to defined module url
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $resultRedirect)
    {
        $enable = $this->helperData->isEnable();
        if ($enable && $this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $configRedirectUrl = $this->registry->registry('bss_force_login_redirect_url');
            return $resultRedirect->setPath($configRedirectUrl);
        } else {
            return $resultRedirect;
        }
    }

    /**
     * Register force login redirect url after login
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param callable $proceed
     * @return callable
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        callable $proceed
    ) {
        if ($this->helperData->isEnable()) {
            $configRedirectUrl = $this->getLoginRedirectUrl();
            $this->registry->register('bss_force_login_redirect_url', $configRedirectUrl);
        }
        return $proceed();
    }

    /**
     * Bss Get Redirect
     *
     * @return string
     */
    public function getLoginRedirectUrl()
    {
        $redirectToDashBoard = $this->helperData->isRedirectDashBoard();
        $currentUrl = $this->helperData->getSessionCatalog()->getBssCurrentUrl();
        if ($this->helperData->getConfigForceLoginPage() == 1) {
            $currentUrl = $this->helperData->getCustomCookie("bss_current_url");
        }
        $previousUrl = $this->helperData->getSessionCatalog()->getBssPreviousUrl();
        $this->helperData->getSessionCatalog()->unsBssCurrentUrl();
        $this->helperData->getSessionCatalog()->unsBssPreviousUrl();
        $configRedirectUrl = $this->helperData->getRedirectUrl();
        if ($configRedirectUrl == "home") {
            return "";
        } elseif ($configRedirectUrl == "previous") {
            if ($currentUrl) {
                return $currentUrl;
            } else {
                return $previousUrl;
            }
        } elseif ($configRedirectUrl == "customurl") {
            return $this->helperData->getCustomUrl();
        } elseif ($configRedirectUrl == "customer/account/index") {
            if ($redirectToDashBoard) {
                return $configRedirectUrl;
            } elseif ($currentUrl) {
                return $currentUrl;
            } else {
                return $previousUrl;
            }
        }
        return '';
    }
}
