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

use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUser;
use Bss\CompanyAccount\Model\SubRole;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class EmailHelper
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailHelper
{
    /**
     * @var GetType
     */
    private $getType;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubRoleRepositoryInterface
     */
    protected $roleRepo;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * Function construct email helper
     *
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param Data $helper
     * @param GetType $getType
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubUserRepositoryInterface $subUserRepository
     * @param StoreManager $storeManager
     * @param SubRoleRepositoryInterface $roleRepo
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder,
        \Bss\CompanyAccount\Helper\Data                    $helper,
        \Bss\CompanyAccount\Helper\GetType                 $getType,
        CustomerRepositoryInterface                        $customerRepository,
        SubUserRepositoryInterface                         $subUserRepository,
        StoreManager                                       $storeManager,
        SubRoleRepositoryInterface                         $roleRepo,
        UrlInterface                                       $urlInterface
    ) {
        $this->getType = $getType;
        $this->customerRepository = $customerRepository;
        $this->subUserRepository = $subUserRepository;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->helper = $helper;
        $this->roleRepo = $roleRepo;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Get customer object
     *
     * @param CustomerInterface|Customer|int $customer
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomer($customer)
    {
        if (is_int($customer)) {
            $customer = $this->customerRepository->getById($customer);
        }

        return $customer;
    }

    /**
     * Get sub-user object
     *
     * @param \Bss\CompanyAccount\Model\SubUser|int $subUser
     * @return SubUserInterface
     * @throws NoSuchEntityException
     */
    private function getSubUser($subUser)
    {
        if (is_int($subUser)) {
            $subUser = $this->subUserRepository->getById($subUser);
        }

        return $subUser;
    }

    /**
     * Send remove notification mail to sub-user
     *
     * @param CustomerInterface|int $customer
     * @param SubUserInterface|int $subUser
     */
    public function sendRemoveNotificationMailToSubUser($customer, $subUser)
    {
        $message = '';
        try {
            $subUser = $this->getSubUser($subUser);
            if ($customer) {
                $customer = $this->getCustomer($customer);
            } else {
                $customer = $this->getCustomer($subUser->getCustomerId());
            }
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $sender = [
                'email' => $this->helper->getEmailSender(),
                'name' => $this->helper->getEmailSenderName(),
            ];
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $this->helper->getRemoveSubUserEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ],
                $sender
            );
        } catch (\Exception $e) {
            $message = __('We can\'t send remove account email to sub-user now.');
        }
        return $message;
    }

    /**
     * Send welcome mail to sub-user
     *
     * @param Customer|CustomerInterface $customer
     * @param \Bss\CompanyAccount\Model\SubUser|SubUserInterface $subUser
     * @throws LocalizedException
     */
    public function sendWelcomeMailToSubUser($customer, $subUser)
    {
        try {
            $customer = $this->getCustomer($customer);
            $subUser = $this->getSubUser($subUser);
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $sender = [
                'email' => $this->helper->getEmailSender(),
                'name' => $this->helper->getEmailSenderName(),
            ];
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $this->helper->getWelcomeSubUserEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ],
                $sender
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send reset password mail to sub-user
     *
     * @param Customer|CustomerInterface $customer
     * @param \Bss\CompanyAccount\Model\SubUser|SubUserInterface $subUser
     */
    public function sendResetPasswordMailToSubUser($customer, $subUser)
    {
        $message = '';
        try {
            $customer = $this->getCustomer($customer);
            $subUser = $this->getSubUser($subUser);
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $sender = [
                'email' => $this->helper->getEmailSender(),
                'name' => $this->helper->getEmailSenderName(),
            ];
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $this->helper->getResetSubUserPasswordEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ],
                $sender
            );
        } catch (\Exception $e) {
            $message = 'We can\'t send reset password email now.';
        }
        return $message;
    }

    /**
     * Send active company account notification for specific customer
     *
     * @param CustomerInterface|\Magento\Customer\Model\Backend\Customer $customer
     * @throws LocalizedException
     */
    public function sendActiveCompanyAccountToCustomer($customer)
    {
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailSender(),
                'name' => $this->helper->getEmailSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                $this->helper->getCaApprovalCopyToEmails(),
                $this->helper->getCompanyAccountApprovalEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getPrefix() . ' ' . $customer->getLastname()
                ],
                $sender
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send deactive company account notification for specific customer
     *
     * @param CustomerInterface|\Magento\Customer\Model\Backend\Customer $customer
     * @throws LocalizedException
     */
    public function sendDeactiveCompanyAccountToCustomer($customer)
    {
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailSender(),
                'name' => $this->helper->getEmailSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                $this->helper->getCaRemoveCopyToEmails(),
                $this->helper->getCompanyAccountRemoveEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getPrefix() . ' ' . $customer->getLastname()
                ],
                $sender
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send order request to admin
     *
     * @param CustomerInterface|int|Customer $customer
     * @param SubUserInterface $subUser
     * @return string
     */
    public function sendOrderRequestToAdmin($customer, $subUser)
    {
        $messageErrorEmail = '';
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailOrderSender(),
                'name' => $this->helper->getEmailOrderSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                null,
                $this->helper->getOrderRequestEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'quote_id' => $subUser->getQuoteId(),
                    'sub_name' => $subUser->getSubEmail()
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send order request email to company admin!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send order placed notification to admin
     *
     * @param CustomerInterface|int|Customer $customer
     * @param int $incrementId
     * @param int $entityId
     * @return string
     * @throws LocalizedException
     */
    public function sendOrderConfirmToAdmin($customer, $incrementId, $entityId)
    {
        $messageErrorEmail = '';
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailOrderSender(),
                'name' => $this->helper->getEmailOrderSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                null,
                $this->helper->getCaOrderConfirmToAdmin(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'increment_id' => $incrementId,
                    'order_id' => $entityId,
                    'sub_email' => $this->helper->getCustomerSession()->getSubUser()->getSubEmail()
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send order confirm email to company admin!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send sub-user info updated to admin
     *
     * @param CustomerInterface|int|Customer $customer
     * @param SubUserInterface $subUser
     * @return Phrase|string
     */
    public function sendSubInfoUpdateToAdmin($customer, $subUser)
    {
        $messageErrorEmail = '';
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailUpdateSender(),
                'name' => $this->helper->getEmailUpdateSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                null,
                $this->helper->getCaSubUserInfoUpdate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'sub_name' => $subUser->getSubName(),
                    'sub_email' => $subUser->getSubEmail(),
                    'sub_role' => $this->roleRepo->getById($subUser->getRoleId())->getRoleName(),
                    'status' => $subUser->getStatusLabel(),
                    'changed' => 'updated'
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send user information update email to admin!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send sub-user info added to admin
     *
     * @param CustomerInterface|int|Customer $customer
     * @param SubUser|array|int $newSubUser
     * @param string $action
     * @return Phrase|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendSubInfoActionToAdmin($customer, $newSubUser, $action)
    {
        $messageErrorEmail = '';
        $subUser="";
        if (gettype($newSubUser) === 'array' && count($newSubUser) < 8) {
            $subUser = $this->subUserRepository->getById($newSubUser['id']);
        }
        if (gettype($newSubUser) === 'array' && count($newSubUser) >= 8) {
            $subUser = $this->subUserRepository->getById($newSubUser['sub_id']);
            $subUser->setSubEmail($newSubUser['sub_email']);
            $subUser->setSubName($newSubUser['sub_name']);
            $subUser->setRoleId((int)$newSubUser['role_id']);
            $subUser->setSubStatus((int)$newSubUser['sub_status']);
        }
        if (gettype($newSubUser) === 'integer') {
            $subUser = $this->subUserRepository->getById($newSubUser);
        }
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer((int)$customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailUpdateSender(),
                'name' => $this->helper->getEmailUpdateSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                null,
                $this->helper->getCaSubUserInfoUpdate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'sub_name' => $subUser->getSubName(),
                    'sub_email' => $subUser->getSubEmail(),
                    'sub_role' => $this->roleRepo->getById($subUser->getRoleId())->getRoleName(),
                    'status' => ($subUser->getSubStatus() == 1) ? 'Enable' : 'Disable',
                    'changed' => $action
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send user information update email to admin!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send sub role updated to admin
     *
     * @param CustomerInterface|int|Customer $customer
     * @param SubRole|array $subRole
     * @return Phrase|string
     */
    public function sendRoleUpdateToAdmin($customer, $subRole)
    {
        $messageErrorEmail = '';
        $roleType = explode(',', $subRole->getRoleType());
        for ($i = 0; $i < count($roleType); $i++) {
            if ($roleType[$i] < 0) {
                unset($roleType[$i]);
            }
        }
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailUpdateSender(),
                'name' => $this->helper->getEmailUpdateSenderName(),
            ];
            $this->sendMail(
                $customer->getEmail(),
                null,
                $this->helper->getCaSubUserRoleUpdate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'role' => $subRole->getRoleName(),
                    'permissions' => implode(', ', $this->removeUnusedRule($roleType)),
                    'changed' => 'updated'
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send user information update email to admin!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send sub role added/delete to admin
     *
     * @param CustomerInterface|int|Customer $customer
     * @param SubRoleInterface|string|array $newSubRole
     * @param string $action
     * @param array|string|SubRoleInterface $roleSendEmail
     * @return Phrase|string
     * @throws LocalizedException
     */
    public function sendRoleActionToAdmin($customer, $newSubRole, $action, $roleSendEmail = null)
    {
        $messageErrorEmail = '';
        if ($action == 'deleted' && $roleSendEmail) {
            $newSubRole = $roleSendEmail;
        } else {
            $newSubRole = $this->roleRepo->getRoleToSendMail($newSubRole);
        }
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $sender = [
                'email' => $this->helper->getEmailUpdateSender(),
                'name' => $this->helper->getEmailUpdateSenderName(),
            ];
            $roleType = $this->removeUnusedRule($newSubRole['role_type']);
            $this->sendMail(
                $customer->getEmail(),
                null,
                $this->helper->getCaSubUserRoleUpdate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'role' => $newSubRole['role_name'],
                    'permissions' => implode(', ', $roleType),
                    'changed' => $action
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send role information update email to admin!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send approved status order to sub-user
     *
     * @param int $orderId
     * @param int $subUserId
     * @param string $status
     * @return Phrase|string
     */
    public function sendOrderStatus($orderId, $subUserId, $status)
    {
        $messageErrorEmail = '';
        try {
            $subUser = $this->getSubUser((int)$subUserId);
            if ($status == 'approve') {
                $statusTemplate = $this->helper->getApprovedOrderEmailTemplate();
            } else {
                $statusTemplate = $this->helper->getRejectedOrderEmailTemplate();
            }
            $sender = [
                'email' => $this->helper->getEmailOrderSender(),
                'name' => $this->helper->getEmailOrderSenderName(),
            ];
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $statusTemplate,
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'id' => $orderId,
                    'name' => $subUser->getSubName(),
                    'order_url' => $this->urlInterface->getUrl('companyaccount/order/view', ['order_id' => $orderId])
                ],
                $sender
            );
        } catch (\Exception $e) {
            $messageErrorEmail = __('We can\'t send order request update email to sub-user!');
        }
        return $messageErrorEmail;
    }

    /**
     * Send email
     *
     * @param string|null $receiver
     * @param string|null $ccMails
     * @param string $mailTemplate
     * @param array $options
     * @param array $vars
     * @param array $sender
     * @return bool
     * @throws LocalizedException
     */
    private function sendMail(
        $receiver = null,
        $ccMails = null,
        $mailTemplate = '',
        $options = [],
        $vars = [],
        $sender = null
    ) {
        try {
            $this->inlineTranslation->suspend();
            $this->transportBuilder
                ->setTemplateIdentifier($mailTemplate)
                ->setTemplateOptions($options)
                ->setTemplateVars($vars)
                ->setFrom($sender)
                ->addTo($receiver);
            if ($ccMails !== null) {
                if (strpos($ccMails, ',') !== false) {
                    $ccMails = explode(',', $ccMails);
                    foreach ($ccMails as $mail) {
                        trim($mail) !== "" ? $this->transportBuilder->addCc(trim($mail)) : false;
                    }
                } else {
                    $this->transportBuilder->addCc(trim($ccMails));
                }
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Exception $e) {
            throw new LocalizedException(__($e));
        }
    }

    /**
     * Remove rule not included
     *
     * @param array $arr
     * @return array
     */
    private function removeUnusedRule ($arr)
    {
        $roleType = [];
        foreach ($arr as $rule) {
            if ($rule > 0) {
                $roleType [] = $rule;
            }
        }
        return $roleType;
    }
}
