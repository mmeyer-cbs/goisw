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
use Bss\CompanyAccount\Api\SubModelRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Create provide sub-user
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class CreateSubUser extends AbstractAuthorization implements ResolverInterface
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
     * @var SubUserInterface
     */
    private $subUserInterface;

    /**
     * Create sub-user constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param PermissionsChecker $permissionsChecker
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserInterface $subUserInterface
     * @param SubModelRepositoryInterface|null $subModelRepository
     */
    public function __construct(
        CustomerRepositoryInterface                      $customerRepository,
        LoggerInterface                                  $logger,
        \Magento\User\Model\UserFactory                  $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        \Magento\User\Model\ResourceModel\User           $userResource,
        PermissionsChecker                               $permissionsChecker,
        SubUserRepositoryInterface                       $subUserRepository,
        SubUserInterface                                 $subUserInterface,
        SubModelRepositoryInterface                      $subModelRepository = null
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
            $subUserRepository,
            $subModelRepository
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
        try {
            $sub = $this->subUserInterface;
            if ($context->getUserType() === self::USER_TYPE_ADMIN
                && array_key_exists('customer_id', $subUser)
                && $this->checkIsCompanyAccountById($subUser['customer_id'])
            ) {
                $sub->setCustomerId($subUser['customer_id']);
            } elseif ($context->getUserType() === self::USER_TYPE_CUSTOMER) {
                $sub->setCustomerId($context->getUserId());
            } else {
                throw new GraphQlInputException(
                    __("Input customer_id is required!")
                );
            }

            $sub->setRoleId($subUser['role_id']);
            $sub->setSubEmail($subUser['sub_email']);
            $sub->setSubName($subUser['sub_name']);
            $sub->setSubStatus($subUser['sub_status']);
            $this->subUserRepository->create($sub);
            return true;
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Create sub-user!")
            );
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

    public function checkIsCompanyAccountById($id)
    {
        if ($this->customerRepository->getById($id)
                ->getCustomAttribute('bss_is_company_account')
                ->getValue() === '1') {
            return true;
        } else {
            return false;
        }
    }
}
