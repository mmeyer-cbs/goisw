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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class UpdateCompanyAccount
 * Update customer to be company account or not
 */
class UpdateCompanyAccount implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    const ADMIN_RESOURCE = "Magento_Customer::customer";
    const IS_COMPANY_ACCOUNT = "bss_is_company_account";

    /**
     * @var \Psr\Log\LoggerInterface
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
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData
     */
    protected $extractCustomerData;

    /**
     * UpdateCompanyAccount constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        CustomerRepositoryInterface $customerRepository,
        \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData $extractCustomerData
    ) {
        $this->logger = $logger;
        $this->userFactory = $userFactory;
        $this->aclPolicy = $aclPolicy;
        $this->customerRepository = $customerRepository;
        $this->extractCustomerData = $extractCustomerData;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->isAllowed($context);
        $this->validate($args);

        try {
            $customer = $this->customerRepository->getById($args['customer_id']);
            $customer->setCustomAttribute(static::IS_COMPANY_ACCOUNT, $args['is_company_account']);

            $this->customerRepository->save($customer);

            return $this->extractCustomerData->execute($customer);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * Validate the admin user
     *
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @return bool
     * @throws GraphQlAuthenticationException
     */
    protected function isAllowed($context)
    {
        $userId = $context->getUserId();
        if ($userId) {
            $user = $this->userFactory->create()->load($userId);
            if ($this->aclPolicy->isAllowed(
                $user->getRole()->getId(),
                static::ADMIN_RESOURCE
            )
            ) {
                return true;
            }
        }

        throw new GraphQlAuthenticationException(__("You have no permission to edit customer to company account."));
    }

    /**
     * Validate input data
     *
     * @throws GraphQlInputException
     */
    protected function validate($args)
    {
        if (!isset($args["customer_id"]) ||
            !$args["customer_id"] ||
            !isset($args['is_company_account'])
        ) {
            throw new GraphQlInputException(__("'customer_id' and 'is_company_account' field must be defined!"));
        }

        if ($args['is_company_account'] != 0 && $args['is_company_account'] != 1) {
            throw new GraphQlInputException(__("is_company_account' value must be between 0 and 1."));
        }
    }
}
