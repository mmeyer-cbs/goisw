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

namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Model\Config\Source\CompanyAccountValue;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class Data
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_company_account/general/enable';
    const XML_PATH_EMAIL_UPDATE_ENABLED = 'bss_company_account/update/enable_update';
    const XML_PATH_EMAIL_ORDER_ENABLED = 'bss_company_account/order/enable_order';
    const XML_ADMIN_EMAIL_SENDER = 'bss_company_account/sub_account/email_sender';
    const XML_ADMIN_EMAIL_UPDATE_SENDER = 'bss_company_account/update/email_sender_update';
    const XML_ADMIN_EMAIL_ORDER_SENDER = 'bss_company_account/order/email_sender_order';
    const XML_PATH_COMPANY_ACCOUNT_APPROVAL_EMAIL_TEMPLATE = 'bss_company_account/sub_account/ca_approval';
    const XML_PATH_APPROVAL_COPY_TO_EMAILS = 'bss_company_account/sub_account/send_approval_copy_to';
    const XML_PATH_COMPANY_ACCOUNT_REMOVE_EMAIL_TEMPLATE = 'bss_company_account/sub_account/ca_remove';
    const XML_PATH_REMOVE_COPY_TO_EMAILS = 'bss_company_account/sub_account/send_remove_copy_to';
    const XML_PATH_WELCOME_SUB_USER_EMAIL_TEMPLATE = 'bss_company_account/sub_account/subuser_welcome';
    const XML_PATH_REMOVE_SUB_USER_EMAIL_TEMPLATE = 'bss_company_account/sub_account/subuser_remove';
    const XML_PATH_RESET_SUB_USER_PASSWORD_EMAIL_TEMPLATE = 'bss_company_account/sub_account/subuser_reset_password';
    const XML_PATH_SUB_USER_INFO_UPDATE_EMAIL_TEMPLATE = 'bss_company_account/update/subuser_info_update';
    const XML_PATH_SUB_USER_ROLE_UPDATE_EMAIL_TEMPLATE = 'bss_company_account/update/subuser_role_update';
    const XML_PATH_COMPANY_ACCOUNT_ORDER_REQUEST_EMAIL_TEMPLATE = 'bss_company_account/order/subuser_order_request';
    const XML_PATH_SUB_USER_ORDER_PLACED_EMAIL_TEMPLATE = 'bss_company_account/order/subuser_order_placed';
    const XML_PATH_COMPANY_ACCOUNT_APPROVED_ORDER_EMAIL_TEMPLATE = 'bss_company_account/order/approve_order_request';
    const XML_PATH_COMPANY_ACCOUNT_REJECTED_ORDER_EMAIL_TEMPLATE = 'bss_company_account/order/reject_order_request';

    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';
    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    const XML_PATH_B2BREGISTRATION_ENABLE_CONFIG = 'b2b/general/enable';

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var SubRoleRepositoryInterface
     */
    protected $roleRepository;

    /**
     * @param HelperData $helperData
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param StoreManager $storeManager
     * @param PhpCookieManager $cookieMetadataManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resource
     * @param CartRepositoryInterface $quoteRepository
     * @param Context $context
     * @param SubRoleRepositoryInterface $roleRepository
     */
    public function __construct(
        HelperData              $helperData,
        RedirectInterface       $redirect,
        ManagerInterface        $messageManager,
        StoreManager            $storeManager,
        PhpCookieManager        $cookieMetadataManager,
        CookieMetadataFactory   $cookieMetadataFactory,
        ScopeConfigInterface    $scopeConfig,
        ResourceConnection      $resource,
        CartRepositoryInterface $quoteRepository,
        Context                 $context,
        SubRoleRepositoryInterface $roleRepository
    ) {
        $this->helperData = $helperData;
        $this->dateTimeFactory = $this->helperData->getDateTimeFactory();
        $this->customerSession = $this->helperData->getCustomerSession();
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->resource = $resource;
        $this->quoteRepository = $quoteRepository;
        $this->roleRepository = $roleRepository;
        parent::__construct($context);
    }

    /**
     * Get Currency object
     *
     * @param string $id
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrency($id = null)
    {
        if ($id) {
            return $this->helperData->getCurrency()->load($id);
        }
        return $this->helperData->getCurrency();
    }

    /**
     * Get base currency
     *
     * @return \Magento\Directory\Model\Currency
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseCurrency()
    {
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        return $this->getCurrency()->load($baseCurrencyCode);
    }

    /**
     * Get date time object
     *
     * @return \Magento\Framework\Intl\DateTimeFactory
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }

    /**
     * Convert amount to specify currency
     *
     * @param float $amount
     * @param bool $toCurrent
     *
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    public function convertCurrency($amount, $toCurrent = true)
    {
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        $rate = $this->storeManager->getStore()->getCurrentCurrencyRate();
        if ($currentCurrencyCode == $baseCurrencyCode) {
            return $amount;
        }
        if ($toCurrent) {
            return $this->getBaseCurrency()->convert($amount, $currentCurrencyCode);
        }
        return (float)$amount / $rate;
    }

    /**
     * Convert format amount
     *
     * @param float $amount
     * @return float|string
     */
    public function convertFormatCurrency($amount)
    {
        return $this->helperData->getPriceHelper()->currency($amount, true);
    }

    /**
     * Get resource object
     *
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get data helper
     *
     * @return HelperData
     */
    public function getDataHelper()
    {
        return $this->helperData;
    }

    /**
     * Retrieve url
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * Check module is enable with website scope
     *
     * @param null|int $website
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEnable($website = null)
    {
        if ($website === null) {
            $website = $this->getStoreManager()->getWebsite()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }

    /**
     * True if customer is company account
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer|null $customer
     * @return bool
     */
    public function isCompanyAccount($customer = null)
    {
        if ($customer == null) {
            $customer = $this->customerSession->getCustomer();
        }
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $companyAccountAttr = $customer->getData('bss_is_company_account');
        } else {
            $companyAccountAttr = $customer->getCustomAttribute('bss_is_company_account');
        }
        if ($companyAccountAttr) {
            return is_string($companyAccountAttr) ?
                (int)$companyAccountAttr === CompanyAccountValue::IS_COMPANY_ACCOUNT :
                (int)$companyAccountAttr->getValue() === CompanyAccountValue::IS_COMPANY_ACCOUNT;
        }
        return false;
    }

    /**
     * Check if can send mail for specific case
     *
     * @param $type
     * @return boolean
     */
    public function isSendEmailEnable($type)
    {
        switch ($type) {
            case "order":
                return $this->scopeConfig->getValue(
                    self::XML_PATH_EMAIL_ORDER_ENABLED,
                    ScopeInterface::SCOPE_STORE
                );
            case "update":
                return $this->scopeConfig->getValue(
                    self::XML_PATH_EMAIL_UPDATE_ENABLED,
                    ScopeInterface::SCOPE_STORE
                );
        }
        return false;
    }

    /**
     * Get email sender
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailSender()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['email'];
    }

    /**
     * Get sender email name
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['name'];
    }

    /**
     * Get email sender
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailUpdateSender()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_UPDATE_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['email'];
    }

    /**
     * Get sender email name
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailUpdateSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_UPDATE_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['name'];
    }

    /**
     * Get email sender
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailOrderSender()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_ORDER_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['email'];
    }

    /**
     * Get sender email name
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailOrderSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_ORDER_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['name'];
    }

    /**
     * Get new company account approval mail template
     *
     * @return string
     */
    public function getCompanyAccountApprovalEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_APPROVAL_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get new company account remove mail template
     *
     * @return string
     */
    public function getCompanyAccountRemoveEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_REMOVE_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get order request mail template
     *
     * @return mixed
     */
    public function getOrderRequestEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_ORDER_REQUEST_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get approved order request email template
     *
     * @return mixed
     */
    public function getApprovedOrderEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_APPROVED_ORDER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get rejected order request email template
     *
     * @return mixed
     */
    public function getRejectedOrderEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_REJECTED_ORDER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get reset sub-user reset password email template
     *
     * @return string
     */
    public function getResetSubUserPasswordEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RESET_SUB_USER_PASSWORD_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get welcome sub-user to company account email template
     *
     * @return string
     */
    public function getWelcomeSubUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WELCOME_SUB_USER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get remove sub-user email template
     *
     * @return string
     */
    public function getRemoveSubUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REMOVE_SUB_USER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get approval emails copy to
     *
     * @return string
     */
    public function getCaApprovalCopyToEmails()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_APPROVAL_COPY_TO_EMAILS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get remove emails copy to
     *
     * @return string
     */
    public function getCaRemoveCopyToEmails()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REMOVE_COPY_TO_EMAILS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get order confirm email
     *
     * @return string
     */
    public function getCaOrderConfirmToAdmin()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUB_USER_ORDER_PLACED_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get update info sub user email
     *
     * @return string
     */
    public function getCaSubUserInfoUpdate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUB_USER_INFO_UPDATE_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get update role sub user email
     *
     * @return string
     */
    public function getCaSubUserRoleUpdate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUB_USER_ROLE_UPDATE_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    public function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    public function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return parent::_getRequest();
    }

    /**
     * Retrieve redirect object
     *
     * @return RedirectInterface
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Retrieve message manager object
     *
     * @return ManagerInterface
     */
    public function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * Retrieve store manager object
     *
     * @return StoreManager
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get current website id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get scope config object
     *
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Retrieve cookie manager
     *
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    public function getCookieManager()
    {
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    public function getCookieMetadataFactory()
    {
        return $this->cookieMetadataFactory;
    }

    /**
     * Get quote by id
     *
     * @param int $id
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteById($id)
    {
        return $this->quoteRepository->get($id);
    }

    /**
     * Send email after action
     *
     * @param string $emailScope
     * @param null|int|string $website
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isSendMail($emailScope, $website = null)
    {
        if ($website === null) {
            $website = $this->getStoreManager()->getWebsite()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            $emailScope,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }
}
