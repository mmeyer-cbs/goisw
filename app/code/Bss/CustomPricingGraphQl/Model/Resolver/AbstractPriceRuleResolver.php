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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricingGraphQl\Model\Resolver;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Class AbstractPriceRuleResolver
 */
abstract class AbstractPriceRuleResolver
{
    const ADMIN_RESOURCE = "Magento_Catalog:products";

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

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
     * GetPriceRule constructor.
     *
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     */
    public function __construct(
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy
    ) {
        $this->priceRuleRepository = $priceRuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->userFactory = $userFactory;
        $this->aclPolicy = $aclPolicy;
    }

    /**
     * Get relation field of price rule to set to price rule on load
     *
     * @return string[]
     */
    protected function getRelationFields(): array
    {
        return ["applied_products", "applied_customers"];
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
        throw new GraphQlAuthenticationException(__("Unauthorized."));
    }

    /**
     * Validate data
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    protected function validate(array $args)
    {
        if (!isset($args['input'])) {
            throw new GraphQlInputException(__("The \"input\" should be defined."));
        }
    }
}
