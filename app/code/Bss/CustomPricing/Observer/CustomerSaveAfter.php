<?php
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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Observer;

use Bss\CustomPricing\Api\AppliedCustomersRepositoryInterface;
use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Bss\CustomPricing\Helper\Data;

/**
 * Check and push validated customer to bss customer applied table
 */
class CustomerSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder =
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AppliedCustomersRepositoryInterface
     */
    protected $appliedCustomersRepository;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver\IndexerResolver
     */
    protected $indexerResolver;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\ProductPrice
     */
    protected $productPriceResource;

    /**
     * @var \Bss\CustomPricing\Helper\IndexHelper
     */
    protected $indexerHelper;

    /**
     * CustomerSaveAfter constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\CustomPricing\Helper\Data $helper
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver\IndexerResolver $indexerResolver
     * @param \Bss\CustomPricing\Model\ResourceModel\ProductPrice $productPriceResource
     * @param AppliedCustomersRepositoryInterface $appliedCustomersRepository
     * @param \Bss\CustomPricing\Helper\IndexHelper $indexerHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Data $helper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver\IndexerResolver $indexerResolver,
        \Bss\CustomPricing\Model\ResourceModel\ProductPrice $productPriceResource,
        AppliedCustomersRepositoryInterface $appliedCustomersRepository,
        \Bss\CustomPricing\Helper\IndexHelper $indexerHelper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceRuleRepository = $priceRuleRepository;
        $this->appliedCustomersRepository = $appliedCustomersRepository;
        $this->customerFactory = $customerFactory;
        $this->indexerResolver = $indexerResolver;
        $this->productPriceResource = $productPriceResource;
        $this->indexerHelper = $indexerHelper;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        try {
            /** @var \Magento\Customer\Model\Data\Customer $customerData */
            $customerData = $observer->getCustomerDataObject();
            if (!$customerData) {
                return $this;
            }
            $customerPerWebsite = $this->helper->isScopeCustomerPerWebsite();
            $websiteId = $customerData->getWebsiteId();
            $customer = $this->customerFactory->create();
            $customer->setData($customerData->__toArray());
            $customer->setId($customerData->getId());
            $searchBuilder = $this->searchCriteriaBuilder->create();
            $priceRules = $this->priceRuleRepository->getList($searchBuilder);
            $needReindexIds = [];
            foreach ($priceRules->getItems() as $rule) {
                if ($customerPerWebsite && $rule->getWebsiteId() != $websiteId) {
                    continue;
                }
                if ($rule->needProcessCustomers()) {
                    $isValidated = $rule->getCustomerConditions()->validate($customer);
                    $appliedId = $this->appliedCustomersRepository->hasCustomer($rule->getId(), $customer->getId());

                    $needReindexIdsByRule = $this->productPriceResource->loadBy($rule->getId());
                    if (!$isValidated && $appliedId) {
                        $this->appliedCustomersRepository->deleteById($appliedId);
                        $needReindexIds = $this->mergeArray($needReindexIds, $needReindexIdsByRule);
                        continue;
                    }

                    if ($isValidated && !$appliedId) {
                        $this->insertOrUpdate($rule, $customer);
                        $isIndexed = $this->indexerResolver->isIndexed(
                            $rule->getId(),
                            ["group_id" => $customer->getGroupId(), "website_id" => $customer->getWebsiteId()]
                        );
                        if (!$isIndexed && !empty($needReindexIdsByRule)) {
                            $needReindexIds = $this->mergeArray($needReindexIds, $needReindexIdsByRule);
                        }
                    }
                }
            }
            $this->indexerHelper->reindex($needReindexIds);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }

    /**
     * Merge array in foreach
     *
     * @param ...$arrays
     * @return array
     */
    private function mergeArray(...$arrays): array
    {
        return array_merge($arrays);
    }

    /**
     * @param \Bss\CustomPricing\Api\Data\PriceRuleInterface $rule
     * @param \Magento\Customer\Model\Customer $customer
     */
    private function insertOrUpdate($rule, $customer)
    {
        try {
            $appliedCustomer = $this->appliedCustomersRepository
                ->getBy($rule->getId(), $customer->getId());
            if (!$appliedCustomer) {
                throw new NoSuchEntityException();
            }
            if ($appliedCustomer->isObjectNew()) {
                $appliedCustomer->setAppliedRule(1);
                $appliedCustomer->setCustomerId($customer->getId());
                $appliedCustomer->setRuleId($rule->getId());
            }
            $appliedCustomer->setCustomerFirstName($customer->getFirstname());
            $appliedCustomer->setCustomerLastName($customer->getlastname());
            $this->appliedCustomersRepository->save($appliedCustomer);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
