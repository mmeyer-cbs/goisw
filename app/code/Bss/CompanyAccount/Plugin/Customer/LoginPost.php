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
use Bss\CompanyAccount\Api\SubUserManagementInterface as SubUserManagement;
use Bss\CompanyAccount\Exception\B2bRegistrationStatusException;
use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LoginPost
 *
 * @package Bss\CompanyAccount\Plugin\customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var SubUserManagement
     */
    private $subUserManagement;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * LoginPost constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param SubUserManagement $subUserManagement
     * @param AccountRedirect $accountRedirect
     * @param Data $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        SubUserManagement $subUserManagement,
        AccountRedirect $accountRedirect,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->redirectFactory = $redirectFactory;
        $this->session = $this->helper->getCustomerSession();
        $this->messageManager = $messageManager;
        $this->subUserManagement = $subUserManagement;
        $this->cookieMetadataManager = $this->helper->getCookieManager();
        $this->cookieMetadataFactory = $this->helper->getCookieMetadataFactory();
        $this->accountRedirect = $accountRedirect;
        $this->registry = $registry;
    }

    /**
     * Check login for sub-user
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param callable $proceed
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, callable $proceed)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $login = $subject->getRequest()->getPost('login');
        try {
            $subUser = $this->subUserManagement->getSubUserBy($login['username'], 'sub_email', $websiteId);
            if ($this->helper->isEnable($websiteId) && $subUser) {
                if ($subject->getRequest()->isPost()) {
                    if (!empty($login['username']) && !empty($login['password'])) {
                        $customer = $this->subUserManagement->getCustomerBySubUser($subUser, $websiteId);
                        if (!$this->formKeyValidator->validate($subject->getRequest())) {
                            $this->session->setUserName($login['username']);
                            $resultRedirect = $this->redirectFactory->create();
                            return $resultRedirect->setPath('*/*/');
                        }

                        $isAuthenticated = $this->subUserManagement->authenticate($subUser, $login['password']);
                        if ($isAuthenticated && !$subUser->getSubStatus()) {
                            $resultRedirect = $this->redirectFactory->create();
                            $this->messageManager->addErrorMessage(
                                __('Your account is inactive. Please contact your company account for more details.')
                            );
                            $this->session->setUsername($login['username']);
                            return $resultRedirect->setPath('*/*/');
                        }
                        if ($isAuthenticated && $subUser->getSubStatus()) {
                            // Unset Customer object data to avoid error when save sub-user object to session file
                            $subUser->unsetData(SubUserInterface::CUSTOMER);
                            $this->session->setSubUser($subUser);
                            $this->session->setCustomerDataAsLoggedIn($customer);
                            $this->processCookieMetaData();

                            return $this->getRedirect();
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('A login and a password are required.'));
                    }
                }
            }
        } catch (B2bRegistrationStatusException $e) {
            $this->session->setUsername($login['username']);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath("*/*/");
        } catch (\Exception $e) {
            $this->session->setUsername($login['username']);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $proceed();
    }

    /**
     * Validate login module
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool|\Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @deprecated Use B2bRegistrationStatusValidator instead
     * @see \Bss\CompanyAccount\Model\B2bRegistrationStatusValidator
     */
    public function validateB2bRegistration($customer)
    {
        if ($this->helper->isModuleOutputEnabled('Bss_B2bRegistration')) {
            if ($this->helper->getScopeConfig()->isSetFlag(
                Data::XML_PATH_B2BREGISTRATION_ENABLE_CONFIG,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->helper->getStoreManager()->getStore()->getId()
            )
            ) {
                $customerAttr = $customer->getCustomAttribute('b2b_activasion_status');
                if ($customerAttr) {
                    $customerAttrValue = $customerAttr->getValue();
                    if ($customerAttrValue ==
                        \Bss\B2bRegistration\Model\Config\Source\CustomerAttribute::B2B_PENDING
                    ) {
                        return __('The associated company account is not verified. Please try again later.');
                    } elseif ($customerAttrValue ==
                        \Bss\B2bRegistration\Model\Config\Source\CustomerAttribute::B2B_REJECT
                    ) {
                        return __('The associated company account is inactive. Please contact your company account for more information.');
                    }
                }
            }
        }

        if (!$this->helper->isCompanyAccount($customer)) {
            return __('You can not access here any more. For more detail, contact your company account.');
        }
        return false;
    }

    /**
     * Redirect by force login module
     *
     * @return bool|\Magento\Framework\Controller\Result\Redirect
     */
    public function forceLoginRedirect()
    {
        if ($this->helper->isModuleOutputEnabled('Bss_ForceLogin')) {
            $forceLoginUrl = $this->registry->registry('bss_force_login_redirect_url');
            if ($forceLoginUrl !== null) {
                return $this->redirectFactory->create()->setPath($forceLoginUrl);
            }
        }
        return false;
    }

    /**
     * Process cookie meta data
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function processCookieMetaData()
    {
        if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * Get redirect after login
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function getRedirect()
    {
        $forceLoginRedirect = $this->forceLoginRedirect();
        if ($forceLoginRedirect !== false) {
            return $forceLoginRedirect;
        }
        $redirectUrl = $this->accountRedirect->getRedirectCookie();
        if (!$this->helper->getScopeConfig()->getValue('customer/startup/redirect_dashboard') &&
            $redirectUrl
        ) {
            $this->accountRedirect->clearRedirectCookie();
            $resultRedirect = $this->redirectFactory->create();
            // URL is checked to be internal in $this->_redirect->success()
            $resultRedirect->setUrl($this->helper->getRedirect()->success($redirectUrl));
            return $resultRedirect;
        }
        return $this->accountRedirect->getRedirect();
    }
}
