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

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface as SubUserQuote;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote as UserQuoteResource;
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Class SubUserQuoteRepository
 *
 * @package Bss\CompanyAccount\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserQuoteRepository implements SubUserQuoteRepositoryInterface
{
    const FIELD_CUSTOMER_ID = 'customer_id';
    const FIELD_SUB_USER_ID = 'sub_id';
    const ADMIN_SUB_BANK = 'NULL';

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var UserQuoteResource
     */
    private $userQuoteResource;

    /**
     * @var UserQuoteResource\CollectionFactory
     */
    private $subQuoteResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubUserQuoteFactory
     */
    private $userQuoteFactory;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * SubUserQuoteRepository constructor.
     *
     * @param LoggerInterface $logger
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param UserQuoteResource\CollectionFactory $subQuoteResource
     * @param SubUserQuoteFactory $userQuoteFactory
     * @param UserQuoteResource $userQuoteResource
     * @param CollectionProcessor $collectionProcessor
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        LoggerInterface                     $logger,
        SearchResultsInterfaceFactory       $searchResultsFactory,
        UserQuoteResource\CollectionFactory $subQuoteResource,
        SubUserQuoteFactory                 $userQuoteFactory,
        UserQuoteResource                   $userQuoteResource,
        CollectionProcessor                 $collectionProcessor,
        CollectionFactory                   $collectionFactory
    ) {
        $this->userQuoteResource = $userQuoteResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
        $this->userQuoteFactory = $userQuoteFactory;
        $this->subQuoteResource = $subQuoteResource;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Save sub-user quote
     *
     * @param SubUserQuote $userQuote
     * @return UserQuoteResource
     * @throws CouldNotSaveException
     */
    public function save(SubUserQuote $userQuote)
    {
        try {
            return $this->userQuoteResource->save($userQuote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __('Could not save quote data.')
            );
        }
    }

    /**
     * Get quote ids by subuser
     *
     * @param int $subId
     * @return array
     * @throws NotFoundException
     */
    public function getBySubUser($subId)
    {
        try {
            /** @var UserQuoteResource\Collection $collection */
            $collection = $this->subQuoteResource->create();
            $collection->addFieldToSelect(SubUserQuote::QUOTE_ID)
                ->addFieldToFilter(SubUserQuote::SUB_USER_ID, $subId);
            return $collection->getColumnValues(SubUserQuote::QUOTE_ID);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NotFoundException(
                __('Could not get data.')
            );
        }
    }

    /**
     * Get sub-quote by entity id
     *
     * @param string|int $id
     * @return SubUserQuote|\Bss\CompanyAccount\Model\SubUserQuote
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        try {
            $userQuote = $this->userQuoteFactory->create();
            $this->userQuoteResource->load($userQuote, $id);

            return $userQuote;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(
                __('Could not get sub-user quote data.')
            );
        }
    }

    /**
     * Get UserQuote by quote id
     *
     * @param string|int $quoteId
     * @return bool|SubUserQuote|\Bss\CompanyAccount\Model\SubUserQuote
     */
    public function getByQuoteId($quoteId)
    {
        try {
            $userQuote = $this->userQuoteFactory->create();
            $this->userQuoteResource->loadByQuoteId((int)$quoteId, $userQuote);
            if ($userQuote->getId()) {
                return $userQuote;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get lists
     *
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->subQuoteResource->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete sub-quote
     *
     * @param SubUserQuote $userQuote
     * @return UserQuoteResource
     * @throws CouldNotDeleteException
     */
    public function delete($userQuote)
    {
        try {
            return $this->userQuoteResource->delete($userQuote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(
                __('Could not delete sub-user quote.')
            );
        }
    }

    /**
     * Delete sub-quote by id
     *
     * @param int|string $id
     * @return UserQuoteResource
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        try {
            $userQuote = $this->userQuoteFactory->create();
            $this->userQuoteResource->load($userQuote, $id);
            return $this->delete($userQuote);
        } catch (CouldNotDeleteException $e) {
            throw new CouldNotDeleteException(
                new \Magento\Framework\Phrase($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(
                __('Could not delete sub-user quote.')
            );
        }
    }

    /**
     * Get UserQuote by user id
     *
     * @param int|string $id
     * @param string $type
     * @return \Magento\Framework\DataObject|false
     */
    public function getByUserId($id, $type)
    {
        try {
            $collection = $this->collectionFactory->create();
            switch ($type) {
                case self::FIELD_CUSTOMER_ID:
                    $collection->addFieldToFilter(
                        'customer_id',
                        $id
                    )->addFieldToFilter('is_back_quote', 2);
                    break;
                case self::FIELD_SUB_USER_ID:
                    $collection->addFieldToFilter(
                        'sub_id',
                        $id
                    )->addFieldToFilter('is_back_quote', 1);
                    break;
                case self::ADMIN_SUB_BANK:
                    $collection->addFieldToFilter(
                        'customer_id',
                        $id
                    )->addFieldToFilter('is_back_quote', 0);
            }
            foreach ($collection->getItems() as $subQuote) {
                if ($subQuote->getId()) {
                    return $subQuote;
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
