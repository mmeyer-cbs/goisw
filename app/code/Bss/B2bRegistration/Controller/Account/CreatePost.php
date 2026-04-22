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

namespace Bss\B2bRegistration\Controller\Account;

use Bss\B2bRegistration\Helper\CreateAccount;
use Bss\B2bRegistration\Helper\CreatePostHelper;
use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Helper\ModuleIntegration;
use Bss\B2bRegistration\Model\Config\Source\AutoApprovalOptions;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Address;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Forward as ResultForward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Class CreatePost
 *
 * @package Bss\B2bRegistration\Controller\Account
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Template email confirm
     */
    const B2B_EMAIL_CONFIRM_CUSTOMER_TEMPLATE = 'b2b_email_setting_customer_confirm_templates';

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CreateAccount
     */
    protected $helperCreateAccount;

    /**
     * @var \Bss\B2bRegistration\Helper\CreatePostHelper
     */
    protected $createPostHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Bss\B2bRegistration\Helper\ModuleIntegration
     */
    private $moduleIntegration;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $eavAttributeFactory;

    /**
     * Company
     */
    protected $company;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @param Context $context
     * @param Data $helper
     * @param CreateAccount $helperCreateAccount
     * @param CreatePostHelper $createPostHelper
     * @param ModuleIntegration $moduleIntegration
     * @param Registry $registry
     * @param ConfigFactory $eavAttributeFactory
     * @param LoggerInterface $logger
     * @param Address $address
     */
    public function __construct(
        Context                                       $context,
        Data                                          $helper,
        CreateAccount                                 $helperCreateAccount,
        \Bss\B2bRegistration\Helper\CreatePostHelper  $createPostHelper,
        \Bss\B2bRegistration\Helper\ModuleIntegration $moduleIntegration,
        \Magento\Framework\Registry                   $registry,
        \Magento\Eav\Model\ConfigFactory              $eavAttributeFactory,
        \Psr\Log\LoggerInterface                      $logger,
        Address $address
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->helperCreateAccount = $helperCreateAccount;
        $this->moduleIntegration = $moduleIntegration;
        $this->createPostHelper = $createPostHelper;
        $this->registry = $registry;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->logger = $logger;
        $this->address=$address;
    }

    /**
     * Add address to customer during create account
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|$addressDataObject;
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }
        $addressForm = $this->helperCreateAccount->getFormFactory()->create(
            'customer_address',
            'customer_register_address'
        );
        $allowedAttributes = $addressForm->getAllowedAttributes();
        $addressData = [];
        $regionDataObject = $this->helperCreateAccount->getRegionDataFactory();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->helperCreateAccount->getDataAddressFactory();
        $this->helper->getDataObject()->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $addressDataObject;
    }

    /**
     * Return customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function returnCustomerSession()
    {
        return $this->helperCreateAccount->getCustomerSessionFactory()->create();
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }

    /**
     * Create B2b account Action
     *
     * @return Redirect
     * @throws CouldNotSaveException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->checkLogin();
        $bbUrl = $this->helper->getB2bUrl();
        if ($bbUrl === '') {
            $bbUrl = 'btwob/account/create';
        }
        if (!$this->getRequest()->isPost()
            || !$this->createPostHelper->returnValidator()->validate($this->getRequest())
        ) {
            $url = $this->createPostHelper->returnUrlFactory()->create()->getUrl('*/*/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }

        //Validate required attributes
        $formData = $this->_request->getParams();

        if ($this->helper->getGenderFieldDefault() == 'req') {
            if (!$this->checkFieldRequire('gender', $formData)) {
                $url = $this->createPostHelper->returnUrlFactory()->create()->getUrl('*/*/create', ['_secure' => true]);
                $resultRedirect->setUrl($this->_redirect->error($url));
                return $resultRedirect;
            }
        }

        if ($this->helper->isEnableAddressField()) {
            $validate = $this->validateAddressField($formData);
            if ($validate !== true) {
                return $validate;
            }
        }

        foreach ($formData as $key => $value) {
            //Check if key contain ca_ to determine if it was Bss CA
            if (strpos($key, 'ca') !== false) {
                $attribute = $this->eavAttributeFactory->create()
                    ->getAttribute('customer', $key);

                $file = str_replace('_value', '', $key);

                $attributeFile = $this->eavAttributeFactory->create()->getAttribute('customer', $file);

                if ($attributeFile) {
                    $type = $attributeFile->getFrontendInput();
                    if (($type === 'file') && $attributeFile->getIsRequired() == 1) {
                        if (empty($this->_request->getFiles($file)['name'])) {
                            $this->messageManager->addError(__("File is required"));
                            $this->logger->critical(__("Value is required for attribute code: ") . $key);
                            $defaultUrl = $this->createPostHelper
                                ->returnUrlFactory()
                                ->create()
                                ->getUrl($bbUrl, ['_secure' => true]);
                            $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
                            return $resultRedirect;
                        }
                    }
                }
                //If Attribute Exist and does required, then check if data is set, else return false;
                if ($attribute) {
                    $type = $attribute->getFrontendInput();
                    if (($type !== 'file') && $attribute->getIsRequired()) {
                        if (empty($value)) {
                            $this->messageManager->addError(__("Value is required for attribute code: ") . $key);
                            $this->logger->critical(__("Value is required for attribute code: ") . $key);
                            $defaultUrl = $this->createPostHelper
                                ->returnUrlFactory()
                                ->create()
                                ->getUrl($bbUrl, ['_secure' => true]);
                            $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
                            return $resultRedirect;
                        }
                    }
                } else {
                    $this->messageManager->addError(__("Attribute does not exist!. Attribute code: ") . $key);
                    $this->logger->critical(__("Attribute does not exist!. Attribute code: ") . $key);
                    $defaultUrl = $this->createPostHelper
                        ->returnUrlFactory()
                        ->create()
                        ->getUrl($bbUrl, ['_secure' => true]);
                    $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
                    return $resultRedirect;
                }
            }
        }

        $autoApproval = $this->helper->isAutoApproval();
        if (isset($formData['bss_is_company_account'])) {
            $isCompany = $formData['bss_is_company_account'];
        } else {
            $isCompany = 0;
        }
        $customerSession = $this->returnCustomerSession();
        $customerSession->regenerateId();

        try {
            $this->registry->unregister('bss_b2b_account');
            $this->registry->register('bss_b2b_account', 'true');
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];
            $customer = $this->helper->getCustomerExtractor()->extract(
                $this->getFormExtract(),
                $this->_request
            );
            $this->company = $this->getRequest()->getParam('company');
            $customer->setAddresses($addresses);
            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $customerSession->getBeforeAuthUrl();
            $this->checkPasswordConfirmation($password, $confirmation);
            $this->saveGroupAttribute($customer);
            $this->storageCustomerStatus($customer, $autoApproval);
            $this->storageCustomerCompanyAccount($customer, $isCompany);
            $customer = $this->createPostHelper->returnAccountManagement()
                ->createAccount($customer, $password, $redirectUrl);
            $this->subcribeCustomer($customer);

            $this->_eventManager->dispatch(
                'bss_customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $resultRedirect = $this->getReturnType($customer, $autoApproval, $resultRedirect);

            return $resultRedirect;
        } catch (StateException $e) {
            $url = $this->createPostHelper->returnUrlFactory()->create()->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t save the customer.'));
        }

        $customerSession->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->createPostHelper
            ->returnUrlFactory()
            ->create()
            ->getUrl($this->helper->getB2bUrl(), ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
        return $resultRedirect;
    }

    /**
     * Get form extract
     *
     * @return string
     */
    protected function getFormExtract()
    {
        if ($this->moduleIntegration->isBssCustomerAttributesModuleEnabled()) {
            return 'b2b_account_create';
        }
        return 'customer_account_create';
    }

    /**
     * Save status for customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param bool $autoApproval
     */
    private function storageCustomerStatus($customer, $autoApproval)
    {
        if ($autoApproval) {
            $customer->setCustomAttribute("b2b_activasion_status", $this->createPostHelper->returnApproval());
        } else {
            $customer->setCustomAttribute("b2b_activasion_status", $this->createPostHelper->returnPending());
        }
    }

    /**
     * Save account is company account for customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param bool $isCompanyAccount
     */
    private function storageCustomerCompanyAccount($customer, $isCompanyAccount)
    {
        if ($isCompanyAccount) {
            if ($isCompanyAccount == 1) {
                $customer->setCustomAttribute("bss_is_company_account", 1);
            } else {
                $customer->setCustomAttribute("bss_is_company_account", 0);
            }
        }
    }

    /**
     * Check Customer Login
     *
     * @return Redirect
     */
    protected function checkLogin()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->returnCustomerSession()->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/index');
        }
        return $resultRedirect;
    }

    /**
     * Check subcribe
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     */
    protected function subcribeCustomer($customer)
    {
        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $this->helperCreateAccount->getSubscriberFactory()->subscribeCustomerById($customer->getId());
        }
    }

    /**
     * Save B2b Customer Group
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     */
    protected function saveGroupAttribute($customer)
    {
        if ($this->helper->isEnable()) {
            $customerGroupId = $this->helper->getCustomerGroup();
            $tax = $this->getRequest()->getPostValue('taxvat');
            $gender = $this->getRequest()->getPostValue('gender');
            $prefix = $this->getRequest()->getPostValue('prefix');
            $middleName = $this->getRequest()->getPostValue('middlename');
            $suffix = $this->getRequest()->getPostValue('suffix');

            if ($tax) {
                $customer->setTaxvat($tax);
            }
            if ($gender) {
                $customer->setGender($gender);
            }
            if ($prefix) {
                $customer->setPrefix($prefix);
            }
            if ($middleName) {
                $customer->setMiddlename($middleName);
            }
            if ($suffix) {
                $customer->setSuffix($suffix);
            }
            if (!$this->helper->isAutoAssigCustomerGroup()) {
                $customer->setGroupId($customerGroupId);
            }
        }
    }

    /**
     * Return success message
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getSuccessMessage()
    {
        if ($this->helperCreateAccount->getAddressHelper()->isVatValidationEnabled()) {
            if ($this->helperCreateAccount->getAddressHelper()
                    ->getTaxCalculationAddressType() == $this->createPostHelper->returnTypeShipping()
            ) {
                // @codingStandardsIgnoreStart
                $message = sprintf(
                    'If you are a registered VAT customer, please <a href="%s">click here</a> to enter your shipping address for proper VAT calculation.',
                    $this->createPostHelper->returnUrlFactory()->create()->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = sprintf(
                    'If you are a registered VAT customer, please <a href="%s">click here</a> to enter your billing address for proper VAT calculation.',
                    $this->createPostHelper->returnUrlFactory()->create()->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $storeName = $this->helper->getStoreName();
            $message = sprintf('Thank you for registering with %s.', $storeName);
        }
        return $message;
    }

    /**
     * @param $customer
     * @param $autoApproval
     * @param $resultRedirect
     * @return ResultForward|Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getReturnType($customer, $autoApproval, $resultRedirect)
    {
        $confirmationStatus = $this->createPostHelper
            ->returnAccountManagement()
            ->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === $this->createPostHelper->returnConfirmRequire()) {
            $emailUrl = $this->helper->getEmailConfirmUrl($customer->getEmail());
            // @codingStandardsIgnoreStart
            $this->messageManager->addSuccess(
                __(
                    'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                    $emailUrl
                )
            );

            $this->sendNewAccountEmail($customer, $autoApproval);

            // @codingStandardsIgnoreEnd
            $url = $this->createPostHelper
                ->returnUrlFactory()
                ->create()
                ->getUrl('customer/account/login', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->success($url));
            return $resultRedirect;
        } elseif ($autoApproval) {
            $this->returnCustomerSession()->setCustomerDataAsLoggedIn($customer);
            $this->sendNewAccountEmail($customer, $autoApproval);
            $this->messageManager->addSuccess(__($this->getSuccessMessage()));
            $resultRedirect = $this->callBackUrl($resultRedirect);
            return $resultRedirect;
        } else {
            $message = $this->helper->getPendingMess();
            $this->messageManager->addSuccess($message);
            $this->sendNewAccountEmail($customer, $autoApproval);
            if ($this->helper->isEnableConfirmEmail()) {
                $this->sendMailConfirmToCustomer($customer);
            }
            $url = $this->createPostHelper
                ->returnUrlFactory()
                ->create()
                ->getUrl('customer/account/login', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->success($url));
            return $resultRedirect;
        }
    }

    /**
     * Call back url
     *
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    protected function callBackUrl($resultRedirect)
    {
        $requestedRedirect = $this->createPostHelper->returnAccountRedirect()->getRedirectCookie();
        if (!$this->helperCreateAccount->getScopeConfig()->getValue('customer/startup/redirect_dashboard') &&
            $requestedRedirect
        ) {
            $resultRedirect->setUrl($this->_redirect->success($requestedRedirect));
            $this->createPostHelper->returnAccountRedirect()->clearRedirectCookie();
            return $resultRedirect;
        }
        return $this->createPostHelper->returnAccountRedirect()->getRedirect();
    }

    /**
     * @param CustomerInterface $customer
     * @param bool $autoApproval
     * @throws NoSuchEntityException
     */
    protected function sendNewAccountEmail($customer, $autoApproval)
    {
        $customerEmail = $customer->getEmail();
        $company = $this->company;
        $storeId = $this->helper->getStoreId();
        $emailTemplate = $this->helper->getAdminEmailTemplate();
        $fromEmail = $this->helper->getAdminEmailSender();
        $recipient = $this->helper->getAdminEmail();
        if ($recipient) {
            $recipient = str_replace(' ', '', $recipient);
            $recipient = (explode(',', $recipient));
            $emailVar = [
                'varEmail' => $customerEmail,
                'varCompany' => $company
            ];

            $adminSendMailStatus = explode(',', $this->helper->isEnableAdminEmail() ?: '');

            if (($autoApproval && in_array(AutoApprovalOptions::AUTO_APPROVE_ACC, $adminSendMailStatus)) ||
                (!$autoApproval && in_array(AutoApprovalOptions::NOT_AUTO_APPROVE_ACC, $adminSendMailStatus))) {
                $this->createPostHelper
                    ->returnBssHelperEmail()
                    ->sendEmail($fromEmail, $recipient, $emailTemplate, $storeId, $emailVar);
            }
        }
    }

    /**
     * Send email confirm to customer
     *
     * @param object $customer
     * @throws NoSuchEntityException
     */
    protected function sendMailConfirmToCustomer($customer)
    {
        $company = $this->company;
        $customerEmail = $customer->getEmail();
        $customerName = $customer->getFirstName() . ' ' . $customer->getLastName();
        $storeId = $this->helper->getStoreId();
        $emailTemplate = self::B2B_EMAIL_CONFIRM_CUSTOMER_TEMPLATE;
        $fromEmail = $this->helper->getAdminEmailSender();
        $recipient = $customerEmail;
        $emailVar = [
            'varName' => $customerName,
            'varCompany' => $company
        ];
        $this->createPostHelper
            ->returnBssHelperEmail()
            ->sendEmail($fromEmail, $recipient, $emailTemplate, $storeId, $emailVar);
    }

    /**
     * Validate address field
     *
     * @param array $formData
     * @return Redirect|true
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateAddressField($formData)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->createPostHelper->returnUrlFactory()->create()->getUrl('*/*/create', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($url));
        $checkMagentoVersion = $this->moduleIntegration->hasMagentoVersion();
        if ($this->helper->getCompanyFieldDefault() == "req" || $checkMagentoVersion) {
            if (!$this->checkFieldRequire("company", $formData)) {
                return $resultRedirect;
            }
        }
        if ($this->helper->getTelephoneFieldDefault() == "req" || $checkMagentoVersion) {
            if (!$this->checkFieldRequire("telephone", $formData)) {
                return $resultRedirect;
            }
        }
        if ($this->helper->getFaxFieldDefault() == "req" || $checkMagentoVersion) {
            if (!$this->checkFieldRequire("fax", $formData)) {
                return $resultRedirect;
            }
        }
        if (!array_key_exists('street', $formData) ||
            (array_key_exists('', array_count_values($formData['street']))
                && array_count_values($formData['street'])[''] === $this->address->getStreetLines())) {
            $this->messageManager->addError(__("Street is required"));
            return $resultRedirect;
        }
        if (!$this->checkFieldRequire("city", $formData)) {
            return $resultRedirect;
        }
        if (!$this->checkRegionRequire($formData)) {
            return $resultRedirect;
        }
        if (!$this->checkFieldRequire("postcode", $formData)) {
            return $resultRedirect;
        }
        if (!$this->checkFieldRequire("country_id", $formData)) {
            return $resultRedirect;
        }
        if ($this->helper->getVatFieldDefault() || $checkMagentoVersion) {
            if ($this->helper->getVatFieldDefault() == "req" && !$this->checkFieldRequire("vat_id", $formData)) {
                return $resultRedirect;
            }
        }
        return true;
    }

    /**
     * Check field
     *
     * @param string $fieldName
     * @param array $listParams
     * @return bool
     */
    public function checkFieldRequire($fieldName, $listParams)
    {
        if (!array_key_exists($fieldName, $listParams) || trim($listParams[$fieldName]) === "") {
            $this->messageManager->addError(__(ucfirst($fieldName) . " is required"));
            return false;
        }
        return true;
    }

    /**
     * Check region require
     *
     * @param $formData
     * @return bool
     */
    public function checkRegionRequire($formData)
    {
        $requireStateCtr = $this->helper->getStateRequired();
        $countryId = $formData['country_id'] ?? '';
        if (in_array($countryId, $requireStateCtr)) {
            if (!array_key_exists('region_id', $formData) || trim($formData['region_id']) === "") {
                $this->messageManager->addError(__("State is required"));
                return false;
            }
        }
        return true;
    }
}
