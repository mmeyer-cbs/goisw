<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_B2bRegistrationGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\B2bRegistrationGraphQl\Model;

use Bss\B2bRegistration\Model\Config\Source\AutoApprovalOptions;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\CreateCustomerAddress as CreateCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Create customer account resolver
 */
class CreateCustomer implements ResolverInterface
{
    const B2B_EMAIL_CONFIRM_CUSTOMER_TEMPLATE = 'b2b_email_setting_customer_confirm_templates';

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var CreateCustomerAccount
     */
    private $createCustomerAccount;

    /**
     * @var Config
     */
    private $newsLetterConfig;

    /**
     * @var CreateCustomerAddressModel
     */
    protected $createCustomerAddress;

    /**
     * @var ExtractCustomerAddressData
     */
    protected $extractCustomerAddressData;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Bss\B2bRegistration\Helper\Data
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var \Bss\B2bRegistration\Helper\Email
     */
    protected $mail;

    /**
     * @var \Bss\B2bRegistration\Helper\CreatePostHelper
     */
    protected $createPostHelper;

    /**
     * @var \Bss\B2bRegistration\Helper\CreateAccount
     */
    protected $helperCreateAccount;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * CreateCustomer constructor.
     *
     * @param \Bss\B2bRegistration\Helper\CreateAccount $helperCreateAccount
     * @param \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Customer $customer
     * @param ExtractCustomerData $extractCustomerData
     * @param CreateCustomerAccount $createCustomerAccount
     * @param Config $newsLetterConfig
     * @param CreateCustomerAddressModel $createCustomerAddress
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     * @param \Bss\B2bRegistration\Helper\Data $moduleHelper
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Bss\B2bRegistration\Helper\Email $mail
     * @param \Bss\B2bRegistration\Helper\CreatePostHelper $createPostHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     */
    public function __construct(
        \Bss\B2bRegistration\Helper\CreateAccount $helperCreateAccount,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customer,
        ExtractCustomerData $extractCustomerData,
        CreateCustomerAccount $createCustomerAccount,
        Config $newsLetterConfig,
        CreateCustomerAddressModel $createCustomerAddress,
        ExtractCustomerAddressData $extractCustomerAddressData,
        \Bss\B2bRegistration\Helper\Data $moduleHelper,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Bss\B2bRegistration\Helper\Email $mail,
        \Bss\B2bRegistration\Helper\CreatePostHelper $createPostHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\SessionFactory $customerSession
    ) {
        $this->helperCreateAccount = $helperCreateAccount;
        $this->customerFactory = $customerFactory;
        $this->customer = $customer;
        $this->newsLetterConfig = $newsLetterConfig;
        $this->extractCustomerData = $extractCustomerData;
        $this->createCustomerAccount = $createCustomerAccount;
        $this->createCustomerAddress = $createCustomerAddress;
        $this->extractCustomerAddressData= $extractCustomerAddressData;
        $this->moduleHelper = $moduleHelper;
        $this->customerRepository = $customerRepository;
        $this->mail = $mail;
        $this->createPostHelper = $createPostHelper;
        $this->eventManager = $eventManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /*Validate input*/
        $this->validateInput($args);
        if (!$this->newsLetterConfig->isActive(ScopeInterface::SCOPE_STORE)) {
            $args['input']['is_subscribed'] = false;
        }
        if (isset($args['input']['date_of_birth'])) {
            $args['input']['dob'] = $args['input']['date_of_birth'];
        }
        $customerSession = $this->customerSession->create();
        $customerSession->setB2bAccount(true);
        /*Create customer*/
        $customer = $this->createCustomerAccount->execute(
            $args['input'],
            $context->getExtensionAttributes()->getStore()
        );
        $customerSession->unsB2bAccount();
        $customer = $this->checkIsSubscribed($customer, $args);
        $customerId = (int)$customer->getId();
        $this->setB2bStatusAccount($customerId);
        /*Set customer group*/
        $customer->setCustomAttribute('b2b_activasion_status', $this->getApprovalValue());
        $groupId = $this->moduleHelper->getCustomerGroup($context->getExtensionAttributes()->getStore()->getId());
        $customer->setGroupId($groupId);
        $this->customerRepository->save($customer);
        /*Set Address information*/
        if ($this->moduleHelper->isEnableAddressField()) {
            $address = $this->createCustomerAddress->execute($customerId, $args['input']['b2b_registration_address']);
            $customer->setAddresses([$address]);
        }
        /*Result output query*/
        $data = $this->extractCustomerData->execute($customer);
        /*Send mail b2b*/
        $this->eventManager->dispatch(
            'bss_customer_register_success',
            ['customer' => $customer]
        );
        $this->sendMail($customer, $this->moduleHelper->isAutoApproval());
        return ['customer' => $data];
    }

    /**
     * Check and set is_subscribed
     *
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @param array $arg
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function checkIsSubscribed($customer, $arg)
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        $isSubscribed = false;
        if (isset($arg['input']['is_subscribed'])) {
            $isSubscribed = $arg['input']['is_subscribed'];
        }
        $extensionAttributes->setIsSubscribed($isSubscribed);
        $customer->setExtensionAttributes($extensionAttributes);
        return $customer;
    }

    /**
     * Set b2b pending
     *
     * @param int $customerId
     * @return void
     * @throws \Exception
     */
    public function setB2bStatusAccount($customerId)
    {
        $customer = $this->customer->load($customerId);
        if ($customer->getId()) {
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('b2b_activasion_status', $this->getApprovalValue());
            $customer->updateData($customerData);
            $customerResource = $this->customerFactory->create();
            $customerResource->saveAttribute($customer, 'b2b_activasion_status');
        }
    }

    /**
     * Get approval value config
     *
     * @return int
     */
    public function getApprovalValue()
    {
        $value =1;
        if ($this->moduleHelper->isAutoApproval()) {
            $value = 2;
        }
        return $value;
    }

    /**
     * Validate input
     *
     * @param array $args
     * @return void
     * @throws GraphQlInputException
     */
    public function validateInput($args)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        if ($this->moduleHelper->isEnableAddressField()) {
            if (empty($args['input']['b2b_registration_address'])) {
                throw new GraphQlInputException(__('"b2b_registration" value should be specified'));
            }
            $address = $args['input']['b2b_registration_address'];
            if (empty($address['telephone'])) {
                throw new GraphQlInputException(__('"telephone" value should be specified'));
            }
            if (empty($address['street'])) {
                throw new GraphQlInputException(__('"street" value should be specified'));
            }
            if (empty($address['city'])) {
                throw new GraphQlInputException(__('"city" value should be specified'));
            }
            if (empty($address['postcode'])) {
                throw new GraphQlInputException(__('"zip/postcode" value should be specified'));
            }
            if (empty($address['country_code'])) {
                throw new GraphQlInputException(__('"country_code" value should be specified'));
            }
            if (empty($address['region']['region_id'])) {
                throw new GraphQlInputException(__('"region/country_code" value should be specified'));
            }
            if (empty($address['region']['region_code'])) {
                throw new GraphQlInputException(__('"region/country_code" value should be specified'));
            }
            if (empty($address['region']['region'])) {
                throw new GraphQlInputException(__('"region/country_code" value should be specified'));
            }
        }
    }

    /**
     * Send mail b2b
     *
     * @param CustomerInterface $customer
     * @param bool $autoApproval
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendMail($customer, $autoApproval)
    {
        $confirmationStatus = $this->createPostHelper
            ->returnAccountManagement()
            ->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === $this->createPostHelper->returnConfirmRequire()) {
            $this->sendNewAccountEmail($customer, $autoApproval);
        } elseif ($autoApproval) {
            $this->helperCreateAccount->getCustomerSessionFactory()->create()->setCustomerDataAsLoggedIn($customer);
            $this->sendNewAccountEmail($customer, $autoApproval);
        } else {
            $this->sendNewAccountEmail($customer, $autoApproval);
            if ($this->moduleHelper->isEnableConfirmEmail()) {
                $this->sendMailConfirmToCustomer($customer);
            }
        }
    }

    /**
     * Send new account Email
     *
     * @param CustomerInterface $customer
     * @param bool $autoApproval
     * @throws NoSuchEntityException
     */
    protected function sendNewAccountEmail($customer, $autoApproval)
    {
        $customerEmail = $customer->getEmail();
        $storeId = $this->moduleHelper->getStoreId();
        $emailTemplate = $this->moduleHelper->getAdminEmailTemplate();
        $fromEmail = $this->moduleHelper->getAdminEmailSender();
        $recipient = $this->moduleHelper->getAdminEmail();
        if ($recipient) {
            $recipient = str_replace(' ', '', $recipient);
            $recipient = (explode(',', $recipient));
            $emailVar = [
                'varEmail' => $customerEmail
            ];

            $adminSendMailStatus = explode(',', $this->moduleHelper->isEnableAdminEmail() ?: '');

            if (($autoApproval && in_array(AutoApprovalOptions::AUTO_APPROVE_ACC, $adminSendMailStatus)) ||
                (!$autoApproval && in_array(AutoApprovalOptions::NOT_AUTO_APPROVE_ACC, $adminSendMailStatus))) {
                $this->mail->sendEmail($fromEmail, $recipient, $emailTemplate, $storeId, $emailVar);
            }
        }
    }

    /**
     * Send email confirm to customer
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    protected function sendMailConfirmToCustomer($customer)
    {
        $customerEmail = $customer->getEmail();
        $customerName = $customer->getFirstName() . ' ' . $customer->getLastName();
        $storeId = $this->moduleHelper->getStoreId();
        $emailTemplate = self::B2B_EMAIL_CONFIRM_CUSTOMER_TEMPLATE;
        $fromEmail = $this->moduleHelper->getAdminEmailSender();
        $recipient = [
            $customerEmail
        ];
        $emailVar = [
            'varName' => $customerName
        ];
        $this->mail->sendEmail($fromEmail, $recipient, $emailTemplate, $storeId, $emailVar);
    }
}
