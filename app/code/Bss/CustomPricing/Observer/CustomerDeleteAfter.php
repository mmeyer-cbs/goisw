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
use Bss\CustomPricing\Helper\IndexHelper;
use Magento\Framework\Event\Observer;

/**
 * Check and delete customer to bss customer applied table
 */
class CustomerDeleteAfter implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Bss\CustomPricing\Model\ResourceModel\ProductPrice
     */
    protected $productPriceResource;

    /**
     * @var IndexHelper
     */
    protected $indexHelper;

    /**
     * CustomerSaveAfter constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\CustomPricing\Helper\Data $helper
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param AppliedCustomersRepositoryInterface $appliedCustomersRepository
     * @param \Bss\CustomPricing\Model\ResourceModel\ProductPrice $productPriceResource
     * @param IndexHelper $indexHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomPricing\Helper\Data $helper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceRuleRepositoryInterface $priceRuleRepository,
        AppliedCustomersRepositoryInterface $appliedCustomersRepository,
        \Bss\CustomPricing\Model\ResourceModel\ProductPrice $productPriceResource,
        IndexHelper $indexHelper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceRuleRepository = $priceRuleRepository;
        $this->appliedCustomersRepository = $appliedCustomersRepository;
        $this->productPriceResource = $productPriceResource;
        $this->indexHelper = $indexHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        try {
            /** @var \Magento\Customer\Model\Data\Customer $customerData */
            $customerData = $observer->getCustomer();
            if (!$customerData) {
                return $this;
            }
            $searchBuilder = $this->searchCriteriaBuilder->create();
            $priceRules = $this->priceRuleRepository->getList($searchBuilder);
            $needReindexIds = [];
            foreach ($priceRules->getItems() as $rule) {
                if ($rule->needProcessCustomers()) {
                    $appliedId = $this->appliedCustomersRepository->hasCustomer($rule->getId(), $customerData->getId());
                    if ($appliedId) {
                        $this->appliedCustomersRepository->deleteById($appliedId);
                        // @codingStandardsIgnoreLine
                        $needReindexIds = array_merge(
                            $needReindexIds,
                            $this->productPriceResource->loadBy($rule->getId())
                        );
                    }
                }
            }

            if (empty($needReindexIds)) {
                return $this;
            }

            $this->indexHelper->reindex($needReindexIds);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }
}
