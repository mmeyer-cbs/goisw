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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model;

use Bss\SalesRep\Api\Data\SalesRepOrderInterface;
use Bss\SalesRep\Api\SalesRepOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SalesRepOrderRepository
 *
 * @package Bss\SalesRep\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesRepOrderRepository implements SalesRepOrderRepositoryInterface
{
    /**
     * @var array
     */
    protected $salesRepId = [];

    /**
     * @var SalesRepOrder
     */
    protected $salesRep;

    /**
     * @var SearchResultsInterface
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var ResourceModel\SalesRepOrder\CollectionFactory
     */
    protected $collection;

    /**
     * @var ResourceModel\SalesRepOrder
     */
    protected $salesRepResource;

    /**
     * @var SalesRepOrderFactory
     */
    protected $salesRepFactory;

    /**
     * SalesRepOrderRepository constructor.
     * @param SalesRepOrderFactory $salesRepFactory
     * @param ResourceModel\SalesRepOrder $salesRepResource
     * @param ResourceModel\SalesRepOrder\CollectionFactory $collection
     * @param SearchResultsInterface $searchResultsFactory
     * @param CollectionProcessor $collectionProcessor
     * @param SalesRepOrder $salesRep
     */
    public function __construct(
        \Bss\SalesRep\Model\SalesRepOrderFactory $salesRepFactory,
        \Bss\SalesRep\Model\ResourceModel\SalesRepOrder $salesRepResource,
        \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\CollectionFactory $collection,
        SearchResultsInterface $searchResultsFactory,
        CollectionProcessor $collectionProcessor,
        \Bss\SalesRep\Model\SalesRepOrder $salesRep
    ) {
        $this->salesRepFactory = $salesRepFactory;
        $this->salesRepResource = $salesRepResource;
        $this->salesRep = $salesRep;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collection = $collection;
    }

    /**
     * Get Sales Rep Order by Id
     *
     * @param int $repId
     * @return SalesRepOrder
     * @throws NoSuchEntityException
     */
    public function getById($repId)
    {
        try {
            $repIds = $this->salesRepId[$repId];
            return $this->salesRep->load($repIds, 'id');
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Sales Rep with id "%1" does not exist.', $repId));
        }
    }

    /**
     * Get Sales Rep order by order id
     *
     * @param int $orderId
     * @return SalesRepOrder
     */
    public function getByOrderId($orderId)
    {
        $salesRep = $this->salesRepFactory->create();
        try {
            $repIds = $this->salesRepId[$orderId];
            return $salesRep->load($repIds, 'order_id');
        } catch (\Exception $e) {
            return $salesRep;
        }
    }

    /**
     * Get order ids
     *
     * @param int $id
     * @return mixed
     */
    public function getSize($id)
    {
        $collection = $this->collection->create();
        $collection->addFieldToSelect('*')->addFieldToFilter('order_id', $id);
        return $collection->getSize();

    }

    /**
     * Get list Sales Rep Order
     *
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->collection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete Sales Rep Order
     *
     * @param SalesRepOrderInterface $salesRep
     * @return mixed
     * @throws CouldNotDeleteException
     */
    public function delete(SalesRepOrderInterface $salesRep)
    {
        try {
            return $this->salesRepResource->delete($salesRep);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }

    /**
     * Delete Sales Rep Order by id
     *
     * @param int $id
     * @return mixed
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        try {
            $salesRep = $this->salesRepFactory->create();
            $this->salesRepResource->load($salesRep, $id);

            return $this->delete($salesRep);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }
}
