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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Model\ResourceModel;

use Bss\StoreCredit\Api\HistoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Bss\StoreCredit\Model\HistoryFactory;

/**
 * Class HistoryRepository
 *
 * @package Bss\StoreCredit\Model\ResourceModel
 */
class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var Credit\Collection
     */
    protected $historyCollection;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var array
     */
    private $historyRegistryById = [];

    /**
     * Constructor HistoryRepository
     *
     * @param CollectionProcessor $collectionProcessor
     * @param History\CollectionFactory $historyCollection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        CollectionProcessor $collectionProcessor,
        \Bss\StoreCredit\Model\ResourceModel\History\CollectionFactory $historyCollection,
        SearchResultsInterfaceFactory $searchResultsFactory,
        HistoryFactory $historyFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->historyCollection = $historyCollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->historyFactory = $historyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($historyId)
    {
        if (isset($this->historyRegistryById[$historyId])) {
            return $this->historyRegistryById[$historyId];
        }

        $history = $this->historyFactory->create()->load($historyId);
        if (!$history->getId()) {
            // history does not exist
            throw new NoSuchEntityException(__('History doesn\'t exist'));
        } else {
            $this->historyRegistryById[$historyId] = $history;
        }
        return $history;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->historyCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
