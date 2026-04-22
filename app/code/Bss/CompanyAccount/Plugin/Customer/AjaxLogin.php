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
namespace Bss\CompanyAccount\Plugin\Customer;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Api\SubUserManagementInterface as SubUserManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\Controller\Result;
use Psr\Log\LoggerInterface;

/**
 * Class AjaxLogin
 *
 * @package Bss\CompanyAccount\Plugin\Customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxLogin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $coreHelper;

    /**
     * @var Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SubUserManagement
     */
    private $subUserManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AjaxLogin constructor.
     *
     * @param \Magento\Framework\Json\Helper\Data $coreHelper
     * @param Result\JsonFactory $resultJsonFactory
     * @param Result\RawFactory $resultRawFactory
     * @param SubUserManagement $subUserManagement
     * @param AccountRedirect $accountRedirect
     * @param CookieManagerInterface $cookieManager
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $coreHelper,
        Result\JsonFactory $resultJsonFactory,
        Result\RawFactory $resultRawFactory,
        SubUserManagement $subUserManagement,
        AccountRedirect $accountRedirect,
        CookieManagerInterface $cookieManager,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->coreHelper = $coreHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->subUserManagement = $subUserManagement;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $this->helper->getCookieMetadataFactory();
        $this->logger = $logger;
        $this->accountRedirect = $accountRedirect;
    }

    /**
     * Login with sub-user
     *
     * @param \Magento\Customer\Controller\Ajax\Login $subject
     * @param \Closure $proceed
     *
     * @return mixed
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Ajax\Login $subject,
        \Closure $proceed
    ) {
        if ($this->helper->isEnable()) {
            $httpBadRequestCode = 400;

            /** @var Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            try {
                $credentials = $this->coreHelper->jsonDecode($subject->getRequest()->getContent());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }
            if (!$credentials ||
                $subject->getRequest()->getMethod() !== 'POST' ||
                !$subject->getRequest()->isXmlHttpRequest()
            ) {
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }
            $response = [
                'errors' => false,
                'message' => __('Login successful.')
            ];
            try {
                $websiteId = $this->helper->getWebsiteId();
                $subUser = $this->subUserManagement->getSubUserBy(
                    $credentials['username'],
                    'sub_email',
                    $websiteId
                );
                if ($subUser) {
                    $customer = $this->subUserManagement->getCustomerBySubUser($subUser, $websiteId);
                    $isAuthenticated = $this->subUserManagement->authenticate($subUser, $credentials['password']);
                    if ($isAuthenticated && !$subUser->getSubStatus()) {
                        $response = [
                            'errors' => true,
                            'message' => __(
                                'Your account is inactive. Please contact your company account for more details.'
                            )
                        ];
                    }
                    if ($isAuthenticated && $subUser->getSubStatus()) {
                        // Unset Customer object data to avoid error when save sub-user object to session file
                        $subUser->unsetData(SubUserInterface::CUSTOMER);
                        $this->customerSession->setSubUser($subUser);

                        $this->customerSession->setCustomerDataAsLoggedIn($customer);

                        $this->clearRedirectCookie();
                        $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                        if (!$this->helper->getScopeConfig()->getValue('customer/startup/redirect_dashboard') &&
                            $redirectRoute
                        ) {
                            $response['redirectUrl'] = $this->helper->getRedirect()->success($redirectRoute);
                            $this->getAccountRedirect()->clearRedirectCookie();
                        }
                    }
                } else {
                    return $proceed();
                }
            } catch (LocalizedException $e) {
                $response = [
                    'errors' => true,
                    'message' => $e->getMessage(),
                ];
            } catch (\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' => __('Invalid login or password.'),
                ];
            }
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
        return $proceed();
    }

    /**
     * Process clear cookie data
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    protected function clearRedirectCookie()
    {
        if ($this->cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * Get Account Redirect obj
     *
     * @return AccountRedirect
     */
    protected function getAccountRedirect()
    {
        return $this->accountRedirect;
    }
}
