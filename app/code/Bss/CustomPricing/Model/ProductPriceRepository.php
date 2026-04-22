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

use Bss\CustomPricing\Api\Data\ProductPriceInterface as ProductPriceRule;
use Bss\CustomPricing\Model\ResourceModel\ProductPrice as ProductPriceResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ProductPriceRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductPriceRepository implements \Bss\CustomPricing\Api\ProductPriceRepositoryInterface
{
    /**
     * @var string Message for undefined error exception
     */
    private $errorMessage = "Something went wrong! Please check the log.";

    protected $searchResultsFactory;

    /**
     * @var ProductPriceResource
     */
    protected $productPriceResource;

    /**
     * @var ProductPriceResource\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var ProductPriceFactory
     */
    protected $productPrice;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductPriceFactory
     */
    protected $productPriceFactory;

    /**
     * ProductPriceRepository constructor.
     *
     * @param ProductPriceResource $productPriceResource
     * @param ProductPriceFactory $productPriceFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param ProductPriceResource\CollectionFactory $collectionFactory
     * @param CollectionProcessor $collectionProcessor
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ProductPriceResource $productPriceResource,
        ProductPriceFactory $productPriceFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        ProductPriceResource\CollectionFactory $collectionFactory,
        CollectionProcessor $collectionProcessor,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productPriceResource = $productPriceResource;
        $this->productPriceFactory = $productPriceFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function save(ProductPriceRule $priceRule)
    {
        try {
            return $this->productPriceResource->save($priceRule);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(__($this->errorMessage));
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        try {
            $productPriceRule = $this->productPriceFactory->create();
            $this->productPriceResource->load($productPriceRule, $id);

            if (!$productPriceRule->getId()) {
                throw new NoSuchEntityException(
                    __(
                        'The product price rule with the "%1" ID wasn\'t found. Verify the ID and try again.',
                        $productPriceRule->getId()
                    )
                );
            }
            return $productPriceRule;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NoSuchEntityException(__("Can't get product price"));
        }
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(ProductPriceRule $productPriceRule)
    {
        try {
            return $this->productPriceResource->delete($productPriceRule);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(new \Magento\Framework\Phrase($e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        try {
            $productPrice = $this->getById($id);
            $this->delete($productPrice);
            return true;
        } catch (NoSuchEntityException $e) {
            throw new CouldNotDeleteException(__($this->errorMessage));
        } catch (CouldNotDeleteException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotDeleteException(__($this->errorMessage));
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteByIds($ids)
    {
        try {
            foreach ($ids as $id) {
                $this->deleteById($id);
            }
            return true;
        } catch (CouldNotDeleteException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($this->errorMessage));
        }
    }

    /**
     * @inheritDoc
     */
    public function getBy($ruleId, $productId)
    {
        if (!$ruleId || !$productId) {
            return false;
        }

        $productPrice = $this->productPriceFactory->create();
        $this->productPriceResource->loadBy($ruleId, $productId, $productPrice);
        return $productPrice;
    }

    /**
     * @inheritDoc
     */
    public function hasProduct($ruleId, $productId)
    {
        if (!$ruleId || !$productId) {
            return false;
        }
        try {
            return $this->productPriceResource->hasProduct($ruleId, $productId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }
}
