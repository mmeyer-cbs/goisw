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
use Bss\CompanyAccountGraphQl\Exception\GraphQlCouldNotDeleteException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

/**
 * Delete company account role
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class DeleteRole extends AbstractAuthorization implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    protected $adminResource = "Bss_CompanyAccount::role_delete";

    /**
     * @var \Bss\CompanyAccount\Helper\CheckAssignedRole
     */
    protected $checkAssignedRole;

    /**
     * DeleteRole constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param \Bss\CompanyAccount\Helper\CheckAssignedRole $checkAssignedRole
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
        \Bss\CompanyAccount\Helper\CheckAssignedRole $checkAssignedRole,
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
        $this->checkAssignedRole = $checkAssignedRole;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, ['input' => $args]);
        $this->validate($args);
        try {
            $this->subModelRepository->deleteById($args['role_id']);

            return true;
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Failed to get role. %1", $e)
            );

            throw new GraphQlCouldNotDeleteException(
                __("Could not %1 the %2. Please try again!", 'delete', 'role')
            );
        }
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @throws GraphQlInputException|GraphQlCouldNotDeleteException
     */
    protected function validate(array $args = null)
    {
        if (!isset($args['role_id']) || (!$args['role_id'] && $args['role_id'] !== 0)) {
            throw new GraphQlInputException(
                __("Role id must be defined!")
            );
        }

        if ($args['role_id'] === 0) {
            throw new GraphQlCouldNotDeleteException(
                __("Oh no! You can't delete the admin role.")
            );
        }

        if ($this->checkAssignedRole->beAssigned($args['role_id'])) {
            throw new GraphQlCouldNotDeleteException(
                __("You can\'t delete this role, it was assigned to sub-user(s)")
            );
        }
    }
}
