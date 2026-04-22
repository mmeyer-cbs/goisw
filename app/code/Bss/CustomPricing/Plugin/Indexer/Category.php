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
namespace Bss\CustomPricing\Plugin\Indexer;

use Bss\CustomPricing\Helper\IndexHelper;
use Magento\Framework\Indexer\IndexerInterface;
use Bss\CustomPricing\Model\Indexer\PriceRule;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;

/**
 * Process price rule condition after save category
 *
 * @package Bss\CustomPricing\Plugin\Indexer
 */
class Category
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * @var PriceRule
     */
    protected $priceRuleIndexer;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\CustomPricing\Helper\ProductSave
     */
    protected $helperProductSave;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var IndexHelper
     */
    protected $indexHelper;

    /**
     * Category constructor.
     * @param IndexerInterface $indexer
     * @param PriceRule $priceRuleIndexer
     * @param \Bss\CustomPricing\Helper\Data $helper
     * @param \Bss\CustomPricing\Helper\ProductSave $helperProductSave
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ProductPriceRepositoryInterface $productPriceRepository
     */
    public function __construct(
        IndexerInterface $indexer,
        PriceRule $priceRuleIndexer,
        \Bss\CustomPricing\Helper\Data $helper,
        \Bss\CustomPricing\Helper\ProductSave $helperProductSave,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ProductPriceRepositoryInterface $productPriceRepository,
        IndexHelper $indexHelper
    ) {
        $this->indexer = $indexer;
        $this->priceRuleIndexer = $priceRuleIndexer;
        $this->helper = $helper;
        $this->helperProductSave = $helperProductSave;
        $this->productRepository = $productRepository;
        $this->productPriceRepository = $productPriceRepository;
        $this->indexHelper = $indexHelper;
    }

    /**
     * Process price rules
     *
     * @param \Magento\Catalog\Model\Category $subject
     * @param \Magento\Catalog\Model\Category $result
     * @return \Magento\Catalog\Model\Category
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\Category $subject,
        \Magento\Catalog\Model\Category $result
    ) {
        if (!$this->helper->isEnabled()) {
            return $result;
        }
        /** @var \Magento\Catalog\Model\Category $result */
        $productIds = $result->getChangedProductIds();
        if (!empty($productIds)) {
            $this->applyRules($productIds);
        }
        return $result;
    }

    /**
     * Apply rules
     *
     * @param array $productIds
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function applyRules($productIds)
    {
        $priceRules = $this->helperProductSave->getPriceRules();
        $deletedProductPriceIds = [];
        $needReindexProductPriceIds = [];
        foreach ($priceRules->getItems() as $rule) {
            foreach ($productIds as $productId) {
                $product = $this->getProduct($productId);
                $websiteIds = $product->getWebsiteIds();
                if (!in_array($rule->getWebsiteId(), $websiteIds)) {
                    continue;
                }
                $isValidated = $rule->getConditions()->validate($product);
                $appliedId = $this->productPriceRepository->hasProduct($rule->getId(), $product->getId());

                if (!$isValidated && $appliedId) {
                    $this->productPriceRepository->deleteById($appliedId);
                    $deletedProductPriceIds[] = $appliedId;
                } else {
                    $needReindexProductPriceIds[] = $this->helperProductSave->insertOrUpdateProductPrice($rule, $product);
                }
            }
        }

        if (!empty($needReindexProductPriceIds)) {
            $this->indexHelper->reindex($needReindexProductPriceIds);
        } else if (!empty($deletedProductPriceIds)){
            $this->indexHelper->cleanIndex($deletedProductPriceIds);
        }
        return $this;
    }

    /**
     * Get product
     *
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }
}
