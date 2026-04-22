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

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubModelRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccountGraphQl\Model\Authorization\TokenSubUserContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Class GetSubUsersByRole
 * Get list sub-users were assigned to specific role
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class GetSubUsersByRole extends AbstractAuthorization implements ResolverInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * GetSubUsersByRole constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param PermissionsChecker $permissionsChecker
     * @param SubUserRepositoryInterface $subUserRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
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
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
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
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, ['input' => $args]);
        try {
            $searchResult = $this->subUserRepository->getList(
                $this->searchCriteriaBuilder->addFilter(
                    SubUserInterface::ROLE_ID,
                    $args['role_id']
                )->create(),
                'role'
            );

            $items = [];
            if ($context->getExtensionAttributes()->getIsSubUser()) {
                $authSubUser = $this->subUserRepository->getById(
                    $context->getExtensionAttributes()->getSubUserId()
                );
            }
            foreach ($searchResult->getItems() as $subUser) {
                if (isset($authSubUser) &&
                    $authSubUser->customer()->getId() != $subUser->getCustomerId()
                ) {
                    continue;
                }

                $items[] = $subUser->getData();
            }

            return $items;
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Failed to load sub-user collection by role. %1", $e)
            );
        }

        return [];
    }
}
