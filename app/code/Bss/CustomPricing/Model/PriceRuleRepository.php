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

use Bss\CustomPricing\Api\Data\PriceRuleInterface as PriceRule;
use Bss\CustomPricing\Api\AppliedCustomersRepositoryInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Bss\CustomPricing\Model\ResourceModel\PriceRule as PriceRuleResource;

/**
 * Class PriceRuleRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
//@codingStandardsIgnoreLine
class PriceRuleRepository implements \Bss\CustomPricing\Api\PriceRuleRepositoryInterface
{
    protected $searchResultsFactory;

    /**
     * @var PriceRuleResource
     */
    protected $priceRuleResource;

    /**
     * @var PriceRuleResource\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var PriceRuleFactory
     */
    protected $priceRuleFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var AppliedCustomersRepositoryInterface
     */
    private $appliedCustomersRepository;

    /**
     * @var ProductPriceRepositoryInterface
     */
    private $productPriceRepository;

    /**
     * PriceRuleRepository constructor.
     *
     * @param PriceRuleResource $priceRuleResource
     * @param PriceRuleFactory $priceRuleFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param PriceRuleResource\CollectionFactory $collectionFactory
     * @param CollectionProcessor $collectionProcessor
     * @param \Psr\Log\LoggerInterface $logger
     * @param AppliedCustomersRepositoryInterface $appliedCustomersRepository
     * @param ProductPriceRepositoryInterface $productPriceRepository
     */
    public function __construct(
        PriceRuleResource $priceRuleResource,
        PriceRuleFactory $priceRuleFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        PriceRuleResource\CollectionFactory $collectionFactory,
        CollectionProcessor $collectionProcessor,
        \Psr\Log\LoggerInterface $logger,
        AppliedCustomersRepositoryInterface $appliedCustomersRepository,
        ProductPriceRepositoryInterface $productPriceRepository
    ) {
        $this->priceRuleResource = $priceRuleResource;
        $this->priceRuleFactory = $priceRuleFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
        $this->appliedCustomersRepository = $appliedCustomersRepository;
        $this->productPriceRepository = $productPriceRepository;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceRule $priceRule)
    {
        try {
            $this->priceRuleResource->save($priceRule);

            return $priceRule;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __("Something went wrong while saving the rule data. Please review the error log.")
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id, $with = [])
    {
        try {
            $priceRule = $this->priceRuleFactory->create();
            $this->priceRuleResource->load(
                $priceRule->with($with),
                $id
            );
            return $priceRule;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(__("Can't get Price Rule"));
        }
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria, $with = [])
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->collectionFactory->create()->with($with);
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(PriceRule $priceRule)
    {
        try {
            return $this->priceRuleResource->delete($priceRule);
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
            $rule = $this->getById($id);
            return $this->delete($rule);
        } catch (NoSuchEntityException $e) {
            throw new CouldNotDeleteException(__("Something went wrong! Please check the log."));
        } catch (CouldNotDeleteException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("Something went wrong! Please check the log."));
        }
    }
}
