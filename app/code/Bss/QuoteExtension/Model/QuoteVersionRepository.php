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

use Bss\QuoteExtension\Model\ResourceModel\QuoteVersion as QuoteVersionResource;
use Bss\QuoteExtension\Api\QuoteVersionRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class QuoteVersionRepository
 */
class QuoteVersionRepository implements QuoteVersionRepositoryInterface
{
    /**
     * @var QuoteVersionFactory
     */
    protected $quoteVersion;

    /**
     * @var QuoteVersionResource
     */
    protected $quoteVersionResource;

    /**
     * @var CollectionFactory
     */
    protected $quoteVersionCollection;

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
     * QuoteVersionRepository constructor.
     * @param QuoteVersionResource $quoteVersionResource
     * @param QuoteVersionResource\CollectionFactory $quoteVersionCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteVersionFactory $quoteVersion,
        QuoteVersionResource $quoteVersionResource,
        \Bss\QuoteExtension\Model\ResourceModel\QuoteVersion\CollectionFactory $quoteVersionCollection,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessor $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->quoteVersion = $quoteVersion;
        $this->quoteVersionResource = $quoteVersionResource;
        $this->quoteVersionCollection = $quoteVersionCollection;
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
        $collection = $this->quoteVersionCollection->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function save($quoteVersion)
    {
        try {
            $this->quoteVersionResource->save($quoteVersion);
        } catch (\Exception $exception) {
        } catch (CouldNotSaveException $couldNotSaveException) {
        }
        return $quoteVersion;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $managetQuote = $this->quoteVersion->create();
        $this->quoteVersionResource->load($managetQuote, $id);
        return $managetQuote;
    }

    /**
     * @inheritDoc
     */
    public function getByQuoteId($quoteId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('quote_id', $quoteId);
        $searchCriteria = $searchCriteriaBuilder->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        $quoteVersion = $this->getById($id);
        if ($quoteVersion->getId()) {
            $this->quoteVersionResource->delete($quoteVersion);
            return true;
        }
        return false;
    }

    /**
     * Get all quote old of request for quote
     *
     * @return \Bss\QuoteExtension\Api\QuoteVersionSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getAllQuoteVersion()
    {
        return $this->getList($this->criteriaBuilder->create());
    }

    /**
     * @inheritDoc
     */
    public function delete($quoteVersion)
    {
        try {
            $this->quoteVersionResource->delete($quoteVersion);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

}
