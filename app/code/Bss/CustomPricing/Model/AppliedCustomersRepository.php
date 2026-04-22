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

namespace Bss\CustomPricing\Model;

use Bss\CustomPricing\Api\Data\AppliedCustomersInterface as AppliedCustomers;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Bss\CustomPricing\Model\ResourceModel\AppliedCustomers as AppliedCustomersResource;

/**
 * Class PriceRuleRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AppliedCustomersRepository implements \Bss\CustomPricing\Api\AppliedCustomersRepositoryInterface
{
    protected $searchResultsFactory;

    /**
     * @var AppliedCustomersResource
     */
    protected $appliedCustomersResource;

    /**
     * @var PriceRuleResource\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var AppliedCustomers
     */
    protected $appliedCustomersFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * AppliedCustomersRepository constructor.
     * @param AppliedCustomersResource $appliedCustomersResource
     * @param AppliedCustomersFactory $appliedCustomersFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param AppliedCustomersResource\CollectionFactory $collectionFactory
     * @param CollectionProcessor $collectionProcessor
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        AppliedCustomersResource $appliedCustomersResource,
        AppliedCustomersFactory $appliedCustomersFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        AppliedCustomersResource\CollectionFactory $collectionFactory,
        CollectionProcessor $collectionProcessor,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->appliedCustomersResource = $appliedCustomersResource;
        $this->appliedCustomersFactory = $appliedCustomersFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function save(AppliedCustomers $appliedCustomers)
    {
        try {
            $appliedCustomer = $this->appliedCustomersResource->save($appliedCustomers);
            return $appliedCustomer;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __("Something went wrong while saving the customer data. Please review the error log.")
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        try {
            $appliedCustomer = $this->appliedCustomersFactory->create();
            $this->appliedCustomersResource->load($appliedCustomer, $id);

            return $appliedCustomer;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(__("Can't get Applied Customer"));
        }
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(AppliedCustomers $appliedCustomers)
    {
        try {
            return $this->appliedCustomersResource->delete($appliedCustomers);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(__("Something went wrong! Please check the log."));
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        try {
            $appliedCustomer = $this->getById($id);
            $this->delete($appliedCustomer);
            return true;
        } catch (NoSuchEntityException $e) {
            throw new CouldNotDeleteException(__("Something went wrong! Please check the log."));
        } catch (CouldNotDeleteException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("Something went wrong! Please check the log."));
        }
    }

    /**
     * Get applied customer by rule and customer id
     *
     * @param int $ruleId
     * @param int $customerId
     * @return \Bss\CustomPricing\Model\AppliedCustomers|false
     */
    public function getBy($ruleId, $customerId)
    {
        if (!$ruleId || !$customerId) {
            return false;
        }
        try {
            $customer = $this->appliedCustomersFactory->create();
            $this->appliedCustomersResource->loadBy($ruleId, $customerId, $customer);
            return $customer;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasCustomer($ruleId, $customerId)
    {
        if (!$ruleId || !$customerId) {
            return false;
        }
        try {
            return $this->appliedCustomersResource->hasCustomer($ruleId, $customerId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }
}
