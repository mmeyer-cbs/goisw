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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model\ResourceModel;

use Bss\CompanyCredit\Api\Data\HistoryInterface;
use Bss\CompanyCredit\Api\HistoryRepositoryInterface;
use Bss\CompanyCredit\Model\HistoryFactory;
use Bss\CompanyCredit\Model\ResourceModel\History as HistoryResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Bss\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollection;

class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var History
     */
    protected $historyResource;

    /**
     * @var \Bss\CompanyCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var array
     */
    private $historyRegistryById = [];

    /**
     * @var \Bss\CompanyCredit\Model\History|null
     */
    private $historyList = null;

    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param History $historyResource
     * @param HistoryFactory $historyFactory
     * @param HistoryCollection $historyCollection
     * @param CollectionProcessor $collectionProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        HistoryResource $historyResource,
        HistoryFactory $historyFactory,
        HistoryCollection $historyCollection,
        CollectionProcessor $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->logger = $logger;
        $this->historyResource = $historyResource;
        $this->historyFactory = $historyFactory;
        $this->historyCollection = $historyCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Get history by id
     *
     * @param int $historyId
     * @return \Bss\CompanyCredit\Model\History|HistoryRepository|mixed
     */
    public function getById($historyId)
    {
        try {
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
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return null;
        }
    }

    /**
     * Get list history
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->historyCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Save history
     *
     * @param HistoryInterface $historyInterface
     * @return void
     */
    public function save(HistoryInterface $historyInterface)
    {
        try {
            $this->historyResource->save($historyInterface);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
