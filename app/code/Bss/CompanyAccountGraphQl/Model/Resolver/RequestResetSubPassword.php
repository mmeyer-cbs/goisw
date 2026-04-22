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

use Bss\CompanyAccount\Api\SubModelRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\ActionHelper;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class RequestResetSubPassword extends AbstractAuthorization implements ResolverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ActionHelper
     */
    private $actionHelper;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * Constructor function
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param PermissionsChecker $permissionsChecker
     * @param SubUserRepositoryInterface $subUserRepository
     * @param Data $helper
     * @param ActionHelper $actionHelper
     * @param SubUserHelper $subUserHelper
     * @param EmailHelper $emailHelper
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
        Data                                             $helper,
        ActionHelper                                     $actionHelper,
        SubUserHelper                                    $subUserHelper,
        EmailHelper                                      $emailHelper,
        SubModelRepositoryInterface                      $subModelRepository = null
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
        $this->customerRepositoryInterface = $customerRepository;
        $this->helper = $helper;
        $this->actionHelper = $actionHelper;
        $this->subUserHelper = $subUserHelper;
        $this->emailHelper = $emailHelper;
    }

    /**
     * Resolves function for request send email to sub-user
     *
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context, ['user' => $args]);
        $subUser = $this->validate($args);
        if ($subUser['sub_email']) {
            $subEmail = $subUser['sub_email'];
            $subInfo = $this->subUserRepository->getByEmail($subEmail);
            $subUserId = $subInfo->getId();
            $customerId = $subInfo->getCustomerId();
            $customer = $this->customerRepositoryInterface->getById($customerId);
            try {
                if (!$this->helper->isCompanyAccount($customer) ||
                    !$this->helper->isEnable($customer->getWebsiteId())
                ) {
                    throw new InputException(
                        __('You have no access to this action.')
                    );
                }
                if ($context->getUserType() === self::USER_TYPE_CUSTOMER
                    && $context->getUserId() === $customerId
                ) {
                    $message = $this->actionHelper->resetPasswordSubUser(
                        $this->subUserHelper,
                        $this->emailHelper,
                        $customer,
                        $subUserId
                    );
                    if ($message) {
                        throw new InputException(
                            __($message)
                        );
                    }
                    return true;
                } else {
                    throw new InputException(
                        __('Customer and Sub-User didn\'t match.')
                    );
                }
            } catch (InputException $e) {
                throw new InputException(__($e->getMessage()));
            } catch (LocalizedException $e) {
                throw new LocalizedException(__($e->getMessage()));
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
        if (!isset($args['user']) ||
            !isset($args['user']['sub_email'])) {
            throw new GraphQlInputException(
                __("Please enter enough information!")
            );
        } else {
            return $args['user'];
        }
    }
}
