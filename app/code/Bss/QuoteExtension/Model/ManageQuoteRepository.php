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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Bss\QuoteExtension\Model\ResourceModel\ManageQuote as ManageQuoteResource;
use Bss\QuoteExtension\Api\ManageQuoteRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class ManageQuoteRepository
 */
class ManageQuoteRepository implements ManageQuoteRepositoryInterface
{
    /**
     * @var ManageQuoteFactory
     */
    protected $manageQuote;

    /**
     * @var ManageQuoteResource
     */
    protected $manageQuoteResource;

    /**
     * @var CollectionFactory
     */
    protected $manageQuoteCollection;

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
     * ManageQuoteRepository constructor.
     * @param ManageQuoteResource $manageQuoteResource
     * @param ManageQuoteResource\CollectionFactory $manageQuoteCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ManageQuoteFactory $manageQuote,
        ManageQuoteResource $manageQuoteResource,
        \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory $manageQuoteCollection,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessor $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->manageQuote = $manageQuote;
        $this->manageQuoteResource = $manageQuoteResource;
        $this->manageQuoteCollection = $manageQuoteCollection;
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
        $collection = $this->manageQuoteCollection->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function save($manageQuote)
    {
        if (!$manageQuote->getEntityId()) {
            return null;
        }
        try {
            $this->manageQuoteResource->save($manageQuote);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save manage quote: %1',
                    $exception->getMessage()
                )
            );
        } catch (CouldNotSaveException $couldNotSaveException) {
            throw new CouldNotSaveException(
                __(
                    'Could not save manage quote: %1',
                    $couldNotSaveException->getMessage()
                )
            );
        }
        return $manageQuote;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $manageQuote = $this->manageQuote->create();
        $this->manageQuoteResource->load($manageQuote, $id);
        return $manageQuote;
    }

    /**
     * Get manage quote by quote id
     *
     * @param $quoteId
     * @return ManageQuote
     */
    public function getByQuoteId($quoteId)
    {
        $manageQuote = $this->manageQuote->create();
        $this->manageQuoteResource->load($manageQuote, $quoteId, 'quote_id');
        return $manageQuote;
    }

    /**
     * @inheritDoc
     */
    public function getByCustomerId($customerId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('main_table.customer_id', $customerId);
        $searchCriteria = $searchCriteriaBuilder->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        $manageQuote = $this->getById($entityId);
        if ($manageQuote->getEntityId()) {
            $this->manageQuoteResource->delete($manageQuote);
            return true;
        }
        return false;
    }

    /**
     * Get all quote old of request for quote
     *
     * @return \Bss\QuoteExtension\Api\ManageQuoteSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getAllManageQuote()
    {
        return $this->getList($this->criteriaBuilder->create());
    }

    /**
     * @inheritDoc
     */
    public function delete($manageQuote)
    {
        try {
            $this->manageQuoteResource->delete($manageQuote);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

}
