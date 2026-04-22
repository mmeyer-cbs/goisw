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

use Bss\CustomPricing\Api\AppliedCustomersRepositoryInterface;
use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Class GetPriceRuleByCustomer
 * Get price rule list for customer
 */
class GetPriceRuleByCustomer extends AbstractPriceRuleResolver implements ResolverInterface
{
    /**
     * @var AppliedCustomersRepositoryInterface
     */
    private $appliedCustomersRepository;

    /**
     * GetPriceRuleByCustomer constructor.
     *
     * @param AppliedCustomersRepositoryInterface $appliedCustomersRepository
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     */
    public function __construct(
        AppliedCustomersRepositoryInterface $appliedCustomersRepository,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy
    ) {
        $this->appliedCustomersRepository = $appliedCustomersRepository;
        parent::__construct($priceRuleRepository, $searchCriteriaBuilder, $logger, $userFactory, $aclPolicy);
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validate($args);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter("customer_id", $args["customer_id"])
            ->create();
        $customers = $this->appliedCustomersRepository->getList($searchCriteria)->getItems();
        $ruleIds = [];

        foreach ($customers as $customer) {
            $ruleIds[] = $customer->getRuleId();
        }

        if (empty($ruleIds)) {
            return [];
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter("id", $ruleIds, "in")
            ->create();

        return $this->priceRuleRepository
            ->getList($searchCriteria, $this->getRelationFields())
            ->getItems();
    }

    /**
     * Validate input
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    protected function validate($args)
    {
        if (!isset($args["customer_id"]) || !$args["customer_id"]) {
            throw new GraphQlInputException(__("Field \"customer_id\" should be defined."));
        }
    }
}
