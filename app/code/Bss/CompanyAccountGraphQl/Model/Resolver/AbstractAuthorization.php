<?php
declare(strict_types=1);
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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccountGraphQl\Model\Resolver;

use Bss\CompanyAccount\Api\SubModelRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccountGraphQl\Model\Authorization\TokenSubUserContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Abstract authorizer class for role and sub-user
 * @SuppressWarnings(CouplingBetweenObjects)
 */
abstract class AbstractAuthorization
{
    const IS_COMPANY_ACCOUNT = 1;
    const SUB_USER_RESOURCE = Permissions::MANAGE_SUB_USER_AND_ROLES;

    /**
     * Authorize admin user
     *
     * @var string
     * @see isAllowed
     */
    protected $adminResource = "Bss_CompanyAccount::company_account";

    /**#@+
     * User type
     */
    const USER_TYPE_INTEGRATION = UserContextInterface::USER_TYPE_INTEGRATION;
    const USER_TYPE_ADMIN = UserContextInterface::USER_TYPE_ADMIN;
    const USER_TYPE_CUSTOMER = UserContextInterface::USER_TYPE_CUSTOMER;
    const USER_TYPE_GUEST = UserContextInterface::USER_TYPE_GUEST;
    const USER_TYPE_SUB_USER = TokenSubUserContext::USER_TYPE_SUB_USER;
    /**#@-*/

    /**
     * Authorized admin user
     *
     * @var \Magento\User\Model\User
     */
    protected $user;

    /**
     * Authorized customer
     *
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @var \Magento\Framework\Authorization\PolicyInterface
     */
    protected $aclPolicy;

    /**
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $userResource;

    /**
     * @var SubModelRepositoryInterface
     */
    protected $subModelRepository;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * AbstractAuthorization constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param PermissionsChecker $permissionsChecker
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubModelRepositoryInterface|null $subModelRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        \Magento\User\Model\ResourceModel\User $userResource,
        PermissionsChecker $permissionsChecker,
        SubUserRepositoryInterface $subUserRepository,
        SubModelRepositoryInterface $subModelRepository = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->userFactory = $userFactory;
        $this->aclPolicy = $aclPolicy;
        $this->userResource = $userResource;
        $this->subModelRepository = $subModelRepository;
        $this->permissionsChecker = $permissionsChecker;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Can user/customer have permission to action
     *
     * @throws GraphQlAuthorizationException|\Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function isAllowed($context, array $args = null)
    {
        $canAccess = false;
        if ($context->getExtensionAttributes()->getIsSubUser()) {
            $canAccess = $this->permissionsChecker->canSubUserAccess(
                $context->getExtensionAttributes()->getSubUserId(),
                static::SUB_USER_RESOURCE
            );

            if (!$canAccess) {
                throw new GraphQlAuthorizationException(
                    __("Oh no! Unauthorized! You can't do this!")
                );
            }
        }

        if ($context->getUserType() === self::USER_TYPE_ADMIN) {
            $this->adminAllowed($context, $canAccess);
        }

        if ($context->getUserType() === self::USER_TYPE_CUSTOMER ||
            $context->getExtensionAttributes()->getIsSubUser()
        ) {
            $this->customerAllowed($context, $canAccess, $args);
        }

        // Guest or integration -> false
        if (!isset($canAccess) || !$canAccess) {
            throw new GraphQlAuthorizationException(
                __("Oh no! Unauthorized! You can't do this!")
            );
        }
    }

    /**
     * Admin authorizing
     *
     * @param ContextInterface $context
     * @param bool $canAccess
     */
    protected function adminAllowed($context, bool &$canAccess)
    {
        $user = $this->getUser($context);
        if ($user) {
            $canAccess = $this->aclPolicy->isAllowed($user->getRole()->getId(), $this->adminResource);
        }
    }

    /**
     * Customer and sub-user authorizing
     *
     * @param ContextInterface $context
     * @param bool $canAccess
     * @param array|null $args
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function customerAllowed($context, bool &$canAccess, array $args = null)
    {
        $user = $this->getCustomer($context);
        if ($context->getExtensionAttributes()->getIsSubUser()) {
            $authSubUser = $this->subUserRepository->getById($context->getExtensionAttributes()->getSubUserId());
            $user = $authSubUser->customer()->getDataModel();
        }
        $companyAccountAttr = $user->getCustomAttribute('bss_is_company_account');
        if (isset($args['input'])) {
            $model = $this->getModel($args['input']);
        }

        // New customer -> escape
        if (!isset($model) || !$model->getCustomerId()) {
            $canAccess = true;
            return;
        }

        $canAccess = $user->getId() &&
            (int) $user->getId() === (int) $model->getCustomerId() &&
            (int) $companyAccountAttr->getValue() === self::IS_COMPANY_ACCOUNT;
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function getModel(array $input)
    {
        $id = null;
        if (isset($input['role_id'])) {
            $id = $input['role_id'];
        }

        if (isset($input['sub_id'])) {
            $id = $input['sub_id'];
        }

        if ($id) {
            return $this->subModelRepository->getById($id);
        }

        return null;
    }

    /**
     * Get authorized customer
     *
     * @param ContextInterface $context
     * @return CustomerInterface|null
     */
    public function getCustomer(ContextInterface $context): ?CustomerInterface
    {
        $userId = $context->getUserId();
        if ($context->getUserType() !== self::USER_TYPE_CUSTOMER || !$userId) {
            return null;
        }

        if (!$this->customer) {
            try {
                $this->customer = $this->customerRepository->getById($userId);

                if (!$this->customer->getId()) {
                    $this->customer = null;
                }
            } catch (\Exception $e) {
                $this->customer = null;
            }
        }

        return $this->customer;
    }

    /**
     * Get access user
     *
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @return \Magento\User\Model\User|null
     */
    public function getUser($context): ?\Magento\User\Model\User
    {
        $userId = $context->getUserId();

        if (!$userId) {
            return null;
        }

        if (!$this->user) {
            $this->user = $this->userFactory->create();
            $this->userResource->load($this->user, $userId);

            if (!$this->user->getId()) {
                $this->user = null;
            }
        }

        return $this->user;
    }
}
