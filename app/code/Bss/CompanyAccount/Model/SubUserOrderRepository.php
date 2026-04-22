<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\Data\SubUserOrderInterface as SubUserOrder;
use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Bss\CompanyAccount\Model\ResourceModel\SubUserOrder as UserOrderResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class SubUserOrderRepository
 *
 * @package Bss\CompanyAccount\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserOrderRepository implements SubUserOrderRepositoryInterface
{
    /**
     * @var UserOrderResource
     */
    private $userOrderResource;

    /**
     * @var UserOrderResource\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubUserOrderFactory
     */
    private $userOrderFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    /**
     * SubUserOrderRepository constructor.
     *
     * @param LoggerInterface $logger
     * @param SearchResultsInterfaceFactory $searchResultsFactory ,
     * @param UserOrderResource\CollectionFactory $collectionFactory
     * @param SubUserOrderFactory $userOrderFactory
     * @param UserOrderResource $userOrderResource
     * @param CollectionProcessor $collectionProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        SearchResultsInterfaceFactory $searchResultsFactory,
        UserOrderResource\CollectionFactory $collectionFactory,
        SubUserOrderFactory $userOrderFactory,
        UserOrderResource $userOrderResource,
        CollectionProcessor $collectionProcessor
    ) {
        $this->userOrderResource = $userOrderResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
        $this->userOrderFactory = $userOrderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(SubUserOrder $userOrder)
    {
        try {
            return $this->userOrderResource->save($userOrder);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __('Could not save order data.')
            );
        }
    }

    /**
     * Get order ids by subuser
     *
     * @param int $subId
     * @return array
     * @throws NotFoundException
     */
    public function getBySubUser($subId)
    {
        try {
            /** @var UserOrderResource\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect(SubUserOrder::ORDER_ID)
                ->addFieldToFilter(SubUserOrder::SUB_USER_ID, $subId);
            return $collection->getColumnValues(SubUserOrder::ORDER_ID);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NotFoundException(
                __('Could not get data.')
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        try {
            $userOrder = $this->userOrderFactory->create();
            $this->userOrderResource->load($userOrder, $id);

            return $userOrder;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(
                __('Could not get sub-user order data.')
            );
        }
    }

    /**
     * Get UserOrder by order id
     *
     * @param int $orderId
     * @return SubUserOrder|bool
     */
    public function getByOrderId($orderId)
    {
        try {
            $userOrder = $this->userOrderFactory->create();
            $this->userOrderResource->loadByOrderId($orderId, $userOrder);
            if ($userOrder->getId()) {
                return $userOrder;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @inheritDoc
     */
    public function delete(SubUserOrder $userOrder)
    {
        try {
            return $this->userOrderResource->delete($userOrder);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(
                __('Could not delete sub-user order.')
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        try {
            $userOrder = $this->userOrderFactory->create();
            $this->userOrderResource->load($userOrder, $id);
            return $this->delete($userOrder);
        } catch (CouldNotDeleteException $e) {
            throw new CouldNotDeleteException(
                new \Magento\Framework\Phrase($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(
                __('Could not delete sub-user order.')
            );
        }
    }
}
