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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Bss\QuoteExtension\Model\ResourceModel\QuoteItem as QuoteItemResource;
use Bss\QuoteExtension\Api\QuoteItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class QuoteItemRepository
 */
class QuoteItemRepository implements QuoteItemRepositoryInterface
{
    /**
     * @var QuoteItemFactory
     */
    protected $quoteItem;

    /**
     * @var QuoteItemResource
     */
    protected $quoteItemResource;

    /**
     * @var CollectionFactory
     */
    protected $quoteItemCollection;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * QuoteItemRepository constructor.
     *
     * @param QuoteItemFactory $quoteItem
     * @param QuoteItemResource $quoteItemResource
     * @param QuoteItemResource\CollectionFactory $quoteItemCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteItemFactory $quoteItem,
        QuoteItemResource $quoteItemResource,
        \Bss\QuoteExtension\Model\ResourceModel\QuoteItem\CollectionFactory $quoteItemCollection,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessor $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->quoteItem = $quoteItem;
        $this->quoteItemResource = $quoteItemResource;
        $this->quoteItemCollection = $quoteItemCollection;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->quoteItemCollection->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function save($quoteItem)
    {
        try {
            $this->quoteItemResource->save($quoteItem);
        } catch (\Exception $exception) {
        } catch (CouldNotSaveException $couldNotSaveException) {
        }
        return $quoteItem;
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId)
    {
        $manaGetQuote = $this->quoteItem->create();
        $this->quoteItemResource->load($manaGetQuote, $entityId);
        return $manaGetQuote;
    }

    /**
     * @inheritDoc
     */
    public function getByItemId($customerId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('item_id', $customerId);
        $searchCriteria = $searchCriteriaBuilder->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        $quoteItem = $this->getById($entityId);
        if ($quoteItem->getID()) {
            $this->quoteItemResource->delete($quoteItem);
            return true;
        }
        return false;
    }

    /**
     * Get all quote old of request for quote
     *
     * @return \Bss\QuoteExtension\Api\QuoteItemSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getAllQuoteItem()
    {
        return $this->getList($this->criteriaBuilder->create());
    }

    /**
     * @inheritDoc
     */
    public function delete($quoteItem)
    {
        try {
            $this->quoteItemResource->delete($quoteItem);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

}
