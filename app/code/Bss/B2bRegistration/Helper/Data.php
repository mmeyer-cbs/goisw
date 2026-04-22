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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\B2bRegistration\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\CustomerExtractor;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerExtractor $customerExtractor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface                 $storeManager,
        CustomerUrl                           $customerUrl,
        DataObjectHelper                      $dataObjectHelper,
        CustomerExtractor                     $customerExtractor
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->customerUrl = $customerUrl;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerExtractor = $customerExtractor;
    }

    /**
     * Enable module
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get B2b Url
     * @return string
     */
    public function getB2bUrl()
    {
        $bbUrl = $this->scopeConfig->getValue(
            'b2b/register/b2b_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $bbUrl;
    }

    /**
     * Enable Shortcut Link
     * @return bool
     */
    public function isEnableShortcutLink()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/register/shortcut_link',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Shortcut Link Text
     * @return string
     */
    public function getShortcutLinkText()
    {
        $text = $this->scopeConfig->getValue(
            'b2b/register/shortcut_link_text',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $text;
    }

    /**
     * Get Date Field
     * @return bool
     */
    public function isEnableDateField()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/register/date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Tax Field
     * @return bool
     */
    public function isEnableTaxField()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/register/tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Gender Field
     * @return bool
     */
    public function isEnableGenderField()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/register/gender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Address Field
     * @return bool
     */
    public function isEnableAddressField()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/register/address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Title of Page
     * @return string
     */
    public function getTitle()
    {
        $text = $this->scopeConfig->getValue(
            'b2b/register/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $text;
    }

    /**
     * Get Group Id
     *
     * @param null|int $storeId
     * @return int
     */
    public function getCustomerGroup($storeId = null)
    {
        $group = $this->scopeConfig->getValue(
            'b2b/register/customer_group',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $group;
    }

    /**
     * Get Enable email to Admin
     * @return bool
     */
    public function isEnableAdminEmail()
    {
        return $this->scopeConfig->getValue(
            'b2b/admin_notification/admin_notification_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Enable Email to Customer
     * @return bool
     */
    public function isEnableCustomerEmail($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/email_setting/customer_email_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Enable Auto Approval
     * @return bool
     */
    public function isAutoApproval()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/approval/auto_approval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Disable Regular Register
     * @return bool
     */
    public function disableRegularForm()
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/register/regular_registration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get pending Mess
     * @return string $pendingMess
     */
    public function getPendingMess()
    {
        $pendingMess = $this->scopeConfig->getValue(
            'b2b/approval/pending_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $pendingMess;
    }

    /**
     * Get Email template Id
     * @return string $emailTemplate
     */
    public function getAdminEmailTemplate()
    {
        $emailTemplate = $this->scopeConfig->getValue(
            'b2b/admin_notification/admin_email_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailTemplate;
    }

    /**
     * Get Approval template Id
     *
     * @param null $storeId
     * @return mixed
     */
    public function getCustomerApproveEmailTemplate($storeId = null)
    {
        $customerApproveEmailTemplate = $this->scopeConfig->getValue(
            'b2b/email_setting/customer_approve_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerApproveEmailTemplate;
    }

    /**
     * Get Reject template Id
     * @return string $customerDisapproveEmailTemplate
     */
    public function getCustomerRejectEmailTemplate($storeId = null)
    {
        $customerRejectEmailTemplate = $this->scopeConfig->getValue(
            'b2b/email_setting/customer_disapprove_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerRejectEmailTemplate;
    }

    /**
     * Get Pending template Id
     * @return string $customerPendingEmailTemplate
     */
    public function getCustomerPendingEmailTemplate($storeId = null)
    {
        $customerPendingEmailTemplate = $this->scopeConfig->getValue(
            'b2b/email_setting/customer_pending_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerPendingEmailTemplate;
    }

    /**
     * Get Email Sender in Store
     * @return string $emailSender
     */
    public function getAdminEmailSender()
    {
        $emailSender = $this->scopeConfig->getValue(
            'b2b/admin_notification/admin_email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailSender;
    }

    /**
     * Get Email Recipeints
     * @return string $emailAdmin
     */
    public function getAdminEmail()
    {
        $emailAdmin = $this->scopeConfig->getValue(
            'b2b/admin_notification/admin_recipeints',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailAdmin;
    }

    /**
     * Get Email Sender in Store
     * @return string $customerEmailSender
     */
    public function getCustomerEmailSender()
    {
        $customerEmailSender = $this->scopeConfig->getValue(
            'b2b/email_setting/customer_email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $customerEmailSender;
    }

    /**
     * Get Reject/Disappval Mess
     * @return string
     */
    public function getDisapproveMess()
    {
        $rejectMess = $this->scopeConfig->getValue(
            'b2b/approval/disapprove_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $rejectMess;
    }

    /**
     * Get Prefix Field
     * @return string
     */
    public function isEnablePrefixField()
    {
        $prefix = $this->scopeConfig->getValue(
            'b2b/register/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $prefix;
    }

    /**
     * Get Prefix Option
     * @return string
     */
    public function getPrefixOptions()
    {
        $prefixOptions = $this->scopeConfig->getValue(
            'b2b/register/prefix_options',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $prefixOptions;
    }

    /**
     * Get Suffix Field
     * @return string
     */
    public function isEnableSuffixField()
    {
        $suffix = $this->scopeConfig->getValue(
            'b2b/register/suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $suffix;
    }

    /**
     * Get Suffix Options
     * @return string
     */
    public function getSuffixOptions()
    {
        $suffixOptions = $this->scopeConfig->getValue(
            'b2b/register/suffix_options',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $suffixOptions;
    }

    /**
     * Get Middle Field
     * @return int
     */
    public function isEnableMiddleField()
    {
        $suffix = $this->scopeConfig->getValue(
            'b2b/register/middle',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $suffix;
    }

    /**
     * Get Suffix Field Default Config
     * @return string
     */
    public function getSuffixFieldDefault()
    {
        $suffixDefault = $this->scopeConfig->getValue(
            'customer/address/suffix_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $suffixDefault;
    }

    /**
     * Get Preffix Field Default Config
     * @return string
     */
    public function getPreffixFieldDefault()
    {
        $prefixDefault = $this->scopeConfig->getValue(
            'customer/address/prefix_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $prefixDefault;
    }

    /**
     * Get Dob Field Default Config
     * @return string
     */
    public function getDobFieldDefault()
    {
        $dobDefault = $this->scopeConfig->getValue(
            'customer/address/dob_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $dobDefault;
    }

    /**
     * Get Tax Field Default Config
     * @return string
     */
    public function getTaxFieldDefault()
    {
        $taxDefault = $this->scopeConfig->getValue(
            'customer/address/taxvat_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $taxDefault;
    }

    /**
     * Get Gender Field Default Config
     * @return string
     */
    public function getGenderFieldDefault()
    {
        $genderDefault = $this->scopeConfig->getValue(
            'customer/address/gender_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $genderDefault;
    }

    /**
     * Get Telephone Field Default Config
     * @return string
     */
    public function getTelephoneFieldDefault()
    {
        $telephoneDefault = $this->scopeConfig->getValue(
            'customer/address/telephone_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $telephoneDefault;
    }

    /**
     * Get Company Field Default Config
     * @return string
     */
    public function getCompanyFieldDefault()
    {
        $companyDefault = $this->scopeConfig->getValue(
            'customer/address/company_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $companyDefault;
    }

    /**
     * Get Fax Field Default Config
     * @return string
     */
    public function getFaxFieldDefault()
    {
        $faxDefault = $this->scopeConfig->getValue(
            'customer/address/fax_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $faxDefault;
    }

    /**
     * Get Vat Field Default Config
     * @return string
     */
    public function getVatFieldDefault()
    {
        $vatDefault = $this->scopeConfig->getValue(
            'customer/create_account/vat_frontend_visibility',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $vatDefault;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreName()
    {
        return $this->storeManager->getStore()->getFrontendName();
    }

    /**
     * @param string $customerEmail
     * @return string
     */
    public function getEmailConfirmUrl($customerEmail)
    {
        $url = $this->customerUrl->getEmailConfirmationUrl($customerEmail);
        return $url;
    }

    /**
     * @return DataObjectHelper
     */
    public function getDataObject()
    {
        return $this->dataObjectHelper;
    }

    /**
     * @return CustomerExtractor
     */
    public function getCustomerExtractor()
    {
        return $this->customerExtractor;
    }

    /**
     * @return bool
     */
    public function isAutoAssigCustomerGroup()
    {
        $defaultAutoAssign = $this->scopeConfig->getValue(
            'customer/create_account/auto_group_assign',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $defaultAutoAssign;
    }

    /**
     * Enable send mail confirm to customer
     *
     * @return bool
     */
    public function isEnableConfirmEmail()
    {
        return $this->scopeConfig->getValue(
            'b2b/email_setting/enable_confirm_mail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check Is Enable Company Account
     *
     * @return mixed
     */
    public function isEnableCompanyAccount()
    {
        return $this->scopeConfig->getValue(
            'bss_company_account/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check Config "Is Company Account" In BackEnd Enable or Not
     *
     * @return mixed
     */
    public function getCompanyAccount()
    {
        return $this->scopeConfig->getValue(
            'b2b/register/is_company_account',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get State Require for
     *
     * @return array
     */
    public function getStateRequired()
    {
        $stateRequired = $this->scopeConfig->getValue(
            'general/region/state_required',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($stateRequired) {
            return explode(",", $stateRequired);
        }
        return [];
    }
}
