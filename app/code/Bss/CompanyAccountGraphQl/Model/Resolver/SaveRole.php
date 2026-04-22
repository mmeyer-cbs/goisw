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

use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory;
use Bss\CompanyAccount\Api\SubModelRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccountGraphQl\Exception\GraphQlCouldNotSaveException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

/**
 * Create/update company account role
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class SaveRole extends AbstractAuthorization implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var SubRoleInterfaceFactory
     */
    protected $subRoleInterfaceFactory;

    /**
     * SaveRole constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param SubRoleInterfaceFactory $subRoleInterfaceFactory
     * @param PermissionsChecker $permissionsChecker
     * @param SubModelRepositoryInterface|null $subModelRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        \Magento\User\Model\ResourceModel\User $userResource,
        SubRoleInterfaceFactory $subRoleInterfaceFactory,
        PermissionsChecker $permissionsChecker,
        SubUserRepositoryInterface $subUserRepository,
        SubModelRepositoryInterface $subModelRepository = null
    ) {
        parent::__construct(
            $customerRepository,
            $logger,
            $userFactory,
            $aclPolicy,
            $userResource,
            $permissionsChecker,
            $subUserRepository,
            $subModelRepository
        );
        $this->subRoleInterfaceFactory = $subRoleInterfaceFactory;
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, $args);

        if ($customer = $this->getCustomer($context)) {
            $args['input']['customer_id'] = (int) $customer->getId();
        }

        if ($context->getExtensionAttributes()->getIsSubUser()) {
            $subUser = $this->subUserRepository->getById(
                $context->getExtensionAttributes()->getSubUserId()
            );
            $args['input']['customer_id'] = $subUser->getCustomerId();
        }

        $this->validate($args);
        try {
            $input = $args['input'];
            $this->prepareData($input);
            $role = $input['model'] ?? null;

            // Edit or create
            if (!$role || !$role->getRoleId()) {
                $role = $this->subRoleInterfaceFactory->create();
            }

            $role->setCompanyAccount($input['customer_id']);
            $role->setMaxOrderAmount($input['max_order_amount'] ?? null);
            $role->setMaxOrderPerDay($input['order_per_day'] ?? null);
            $role->setRoleName($input['role_name']);
            $role->setRoleType($input['role_type'] ?? "");
            $this->subModelRepository->save($role);

            return $role->getData();
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Create role failed. %1", $e)
            );

            throw new GraphQlCouldNotSaveException(
                __("Could not %1 the %2. Please try again!", "save", "role")
            );
        }
    }

    /**
     * Set the admin resource for admin authorization before authorization run
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAllowed($context, array $args = null)
    {
        $this->adminResource = "Bss_CompanyAccount::role_add";

        // Edit?
        if (isset($args['input']['role_id'])) {
            $this->adminResource = "Bss_CompanyAccount::role_edit";
        }

        parent::isAllowed($context, $args);
    }

    /**
     * Prepare for valid input data. For create and update too
     *
     * @param array|null $input
     */
    protected function prepareData(array &$input = null)
    {
        // On update
        if (isset($input['role_id']) && $input['role_id']) {
            try {
                $role = $this->subModelRepository->getById($input['role_id']);
                $input['model'] = $role;
                if ($role->getRoleId()) {
                    $this->getUpdateValue($input, $role);
                }
            } catch (\Exception $e) {
                $this->logger->critical(
                    __("BSS.ERROR: Get sub-role failed. %1", $e)
                );
            }
        }

        if (!isset($role) || !$role->getRoleId()) {
            $input['role_type'] = $this->getRoleTypeValue($input['role_type'] ?? "");
        }
    }

    /**
     * Get update values. Compare with current data
     *
     * @param array $input
     * @param DataObject $role
     * @throws GraphQlInputException
     */
    private function getUpdateValue(array &$input, DataObject $role)
    {
        foreach ($role->getData() as $key => $value) {
            // Can't change owner customer
            if ($key === SubRoleInterface::CUSTOMER_ID) {
                $input[$key] = (int) $value;
                continue;
            }

            if (!isset($input[$key])) {
                // No defined field -> get current data
                $input[$key] = $value;
            }

            // if field defined value, validate the new value
            $this->validateField($key, $input[$key]);

            if ($key === SubRoleInterface::TYPE) {
                $input[$key] = $this->getRoleTypeValue($input[$key]);
            }
        }
    }

    /**
     * Get role type value
     *
     * @param string $values
     * @return string
     */
    protected function getRoleTypeValue(string $values): string
    {
        $roleTypes = explode(",", $values);
        foreach ($roleTypes as $index => $type) {
            // No add admin value to role type
            if ((int) $type === 0) {
                unset($roleTypes[$index]);
                continue;
            }
            $roleTypes[$index] = (int) $type;
        }
        return implode(',', $roleTypes);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @throws GraphQlInputException
     */
    public function validateField(string $field, $value)
    {
        $requiredFields = $this->getRequiredFields();

        if (!in_array($field, $requiredFields)) {
            return;
        }

        if (!$value) {
            throw new GraphQlInputException(
                __("%1 must not empty!", $field)
            );
        }
    }

    /**
     * Get required fields
     *
     * @return string[]
     */
    public function getRequiredFields(): array
    {
        return ['customer_id', 'role_name'];
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException|\Magento\Framework\Exception\NoSuchEntityException
     */
    protected function validate(array $args = null)
    {
        if (!isset($args['input'])) {
             throw new GraphQlInputException(__("The `%1` field is required and must be defined!", 'input'));
        }

        $input = $args['input'];

        // is edit, check role existed? if true escape validate field
        if (isset($input['role_id'])) {
            $role = $this->subModelRepository->getById($input['role_id']);

            if (!$role->getRoleId()) {
                throw new GraphQlNoSuchEntityException(
                    __("Role id = %1 not found!", $input['role_id'])
                );
            }

            return;
        }

        // validate on create
        foreach ($this->getRequiredFields() as $field) {
            $this->validateField($field, $input[$field] ?? null);
        }

        try {
            $customer = $this->customerRepository->getById($input['customer_id']);
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Get customer failed. %1", $e)
            );
        }

        if (!isset($customer) || !$customer->getId()) {
            throw new GraphQlNoSuchEntityException(__("The customer with id = %1 not existed!", $input['customer_id']));
        }
    }
}
