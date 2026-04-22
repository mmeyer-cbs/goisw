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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete provide sub-user
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class DeleteSubUser extends AbstractAuthorization implements ResolverInterface
{
    /**
     * @var \Bss\CompanyAccount\Helper\EmailHelper
     */
    protected $emailHelper;

    /**
     * DeleteSubUser constructor.
     *
     * @param \Bss\CompanyAccount\Helper\EmailHelper $emailHelper
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
        \Bss\CompanyAccount\Helper\EmailHelper $emailHelper,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        \Magento\User\Model\ResourceModel\User $userResource,
        PermissionsChecker $permissionsChecker,
        SubUserRepositoryInterface $subUserRepository,
        SubModelRepositoryInterface $subModelRepository = null
    ) {
        $this->emailHelper = $emailHelper;
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
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, ['input' => $args]);
        $this->validate($args);
        try {
            $subId = (int) $args['sub_id'];
            try {
                $this->emailHelper->sendRemoveNotificationMailToSubUser(
                    null,
                    $subId
                );
            } catch (\Exception $e) {
                $this->logger->critical(
                    __("BSS.ERROR: When send email to sub-user. %1", $e)
                );
            }

            $this->subUserRepository->deleteById($subId);

            return true;
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Remove sub-user!")
            );
        }

        return false;
    }

    /**
     * Validate params
     *
     * @throws GraphQlInputException
     */
    public function validate(array $args = null)
    {
        if (!isset($args['sub_id'])) {
            throw new GraphQlInputException(
                __("Field `sub_id` is required!")
            );
        }
    }
}
