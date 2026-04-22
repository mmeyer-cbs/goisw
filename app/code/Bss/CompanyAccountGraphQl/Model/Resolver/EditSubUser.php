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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccountGraphQl\Model\Resolver;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Authorization\PolicyInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;

/**
 * Create provide sub-user
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class EditSubUser extends AbstractAuthorization implements ResolverInterface
{
    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Create sub-user constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param UserFactory $userFactory
     * @param PolicyInterface $aclPolicy
     * @param User $userResource
     * @param PermissionsChecker $permissionsChecker
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserInterface $subUserInterface
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface             $logger,
        UserFactory                 $userFactory,
        PolicyInterface             $aclPolicy,
        User                        $userResource,
        PermissionsChecker          $permissionsChecker,
        SubUserRepositoryInterface  $subUserRepository,
        SubUserInterface            $subUserInterface
    ) {
        $this->subUserRepository = $subUserRepository;
        $this->logger = $logger;
        $this->subUserInterface = $subUserInterface;
        parent::__construct(
            $customerRepository,
            $logger,
            $userFactory,
            $aclPolicy,
            $userResource,
            $permissionsChecker,
            $subUserRepository
        );
    }

    /**
     * Resolves a value for a type or field in a schema
     *
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, ['user' => $args]);
        $subUser = $this->validate($args);
        if ($subUser['sub_id']) {
            $subUserId = $subUser['sub_id'];
            $subInfo = $this->subUserRepository->getById($subUserId);
            $customerId = $subInfo->getCustomerId();
            try {
                if ($context->getUserType() === self::USER_TYPE_ADMIN
                    && array_key_exists('customer_id', $subUser)
                    && $subUser['customer_id'] === $customerId
                ) {
                    $this->saveSubUser($subUser, $subInfo);
                } elseif ($context->getUserType() === self::USER_TYPE_CUSTOMER
                    && $context->getUserId() === $customerId
                ) {
                    $this->saveSubUser($subUser, $subInfo);
                } else {
                    throw new GraphQlInputException(
                        __("Input customer_id is required!")
                    );
                }
                return true;
            } catch (\Exception $e) {
                $this->logger->critical(
                    __("BSS.ERROR: Create sub-user!")
                );
            }
        }
        return false;
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @return array|null
     * @throws GraphQlInputException
     */
    public function validate(array $args = null)
    {
        if (!isset($args['user'])) {
            throw new GraphQlInputException(
                __("Sub-user information is required!")
            );
        } else {
            return $args['user'];
        }
    }

    /**
     * Save sub user function
     *
     * @param array $subUser
     * @throws AlreadyExistsException|NoSuchEntityException
     */
    public function saveSubUser(array $subUser, $subInfo)
    {
        $sub = $subInfo;
        $sub->setRoleId($subUser['role_id']);
        $sub->setSubEmail($subUser['sub_email']);
        $sub->setSubName($subUser['sub_name']);
        $sub->setSubStatus($subUser['sub_status']);
        $this->subUserRepository->save($sub);
    }
}
