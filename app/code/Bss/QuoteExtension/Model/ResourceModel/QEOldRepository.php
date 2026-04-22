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
namespace Bss\QuoteExtension\Model\ResourceModel;

use Bss\QuoteExtension\Api\QEOldRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class QEOldRepository
 */
class QEOldRepository implements QEOldRepositoryInterface
{
    /**
     * @var QEOld
     */
    protected $qEOldResource;

    /**
     * @var CollectionFactory
     */
    protected $qEOldColletion;

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
     * QEOldRepository constructor.
     * @param QEOld\CollectionFactory $qEOldColletion
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ResourceModel\QEOld $qEOldResource,
        \Bss\QuoteExtension\Model\ResourceModel\QEOld\CollectionFactory $qEOldColletion,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessor $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->qEOldResource = $qEOldResource;
        $this->qEOldColletion = $qEOldColletion;
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
        $collection = $this->qEOldColletion->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function save($qEOld)
    {
        try {
            $this->qEOldResource->save($qEOld);
        } catch (\Exception $exception) {
        } catch (CouldNotSaveException $couldNotSaveException) {
        }
        return $qEOld;
    }

    /**
     * Get all quote old of request for quote
     *
     * @return \Bss\QuoteExtension\Api\QEOldSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getAllQEOld()
    {
        return $this->getList($this->criteriaBuilder->create());
    }


    /**
     * @param $qEOld
     * @return void
     */
    public function delete($qEOld)
    {
        try {
            $this->qEOldResource->delete($qEOld);
        } catch (\Exception $exception) {

        }
    }

}
