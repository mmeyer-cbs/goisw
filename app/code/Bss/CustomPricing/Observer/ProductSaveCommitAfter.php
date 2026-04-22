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

namespace Bss\CustomPricing\Observer;

use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;

/**
 * After commit save product
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductSaveCommitAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\CustomPricing\Helper\ProductSave
     */
    protected $helperProductSave;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\CustomPricing\Helper\IndexHelper
     */
    protected $reindexHelper;

    /**
     * ProductSaveCommitAfter constructor.
     *
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param \Bss\CustomPricing\Helper\Data $helper
     * @param \Bss\CustomPricing\Helper\ProductSave $helperProductSave
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param \Bss\CustomPricing\Helper\IndexHelper $reindexHelper
     */
    public function __construct(
        ProductPriceRepositoryInterface $productPriceRepository,
        \Bss\CustomPricing\Helper\Data $helper,
        \Bss\CustomPricing\Helper\ProductSave $helperProductSave,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        \Bss\CustomPricing\Helper\IndexHelper $reindexHelper
    ) {
        $this->productPriceRepository = $productPriceRepository;
        $this->helper = $helper;
        $this->helperProductSave = $helperProductSave;
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->reindexHelper = $reindexHelper;
    }

    /**
     * Validate the saved product and push to the bss price table
     *
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();

        if (!in_array($product->getTypeId(), ['bundle', 'configurable', 'grouped'])) {
            $parentIds = $this->getParentIds((int) $product->getId());
        }

        $websiteIds = $product->getWebsiteIds();
        $priceRules = $this->helperProductSave->getPriceRules();
        $needReindexIds = [];
        foreach ($priceRules->getItems() as $rule) {
            if (!in_array($rule->getWebsiteId(), $websiteIds)) {
                continue;
            }
            $isValidated = $rule->getConditions()->validate($product);
            $isValidated = $this->validateParentProduct($isValidated, $parentIds ?? [], $rule);
            $appliedId = $this->productPriceRepository->hasProduct($rule->getId(), $product->getId());
            if (!$isValidated && $appliedId) {
                $this->productPriceRepository->deleteById($appliedId);
                $this->reindexHelper->cleanIndex(null, (int) $rule->getId(), (int) $product->getId());
                return $this;
            }
            if ($isValidated) {
                $productPriceId = $this->helperProductSave->insertOrUpdateProductPrice($rule, $product);

                if ($productPriceId !== 0) {
                    $needReindexIds[] = $productPriceId;
                }
            }
        }

        if (empty($needReindexIds)) {
            return $this;
        }

        $this->reindexHelper->reindex($needReindexIds);
        return $this;
    }

    /**
     * Get parent ids of provided product id
     *
     * @param int $childId
     * @return array
     */
    protected function getParentIds(int $childId): array
    {
        try {
            $conn = $this->resourceConnection->getConnection();
            $select = $conn->select()->from(
                ["rela" => $this->resourceConnection->getTableName("catalog_product_relation")],
                ["parent_id"]
            )->joinInner(
                ['product' => $this->resourceConnection->getTableName("catalog_product_entity")],
                "rela.parent_id=product.entity_id",
                []
            )->where("rela.child_id IN (?)", $childId);

            $result = $conn->fetchAll($select);

            if (empty($result)) {
                return [];
            }

            return array_column($result, 'parent_id');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Is parent product validate
     *
     * To confirm this saved child product is validated by parent and keep in product price table
     *
     * @param bool $isValidated
     * @param array $parentIds
     * @param \Bss\CustomPricing\Api\Data\PriceRuleInterface $rule
     * @return bool
     */
    private function validateParentProduct(
        bool $isValidated,
        array $parentIds,
        \Bss\CustomPricing\Api\Data\PriceRuleInterface $rule
    ): bool {
        if (!$isValidated && !empty($parentIds)) {
            foreach ($parentIds as $parentId) {
                try {
                    $isValidated = $rule->getConditions()->validate($this->productRepository->getById($parentId));
                    if ($isValidated) {
                        return true;
                    }
                } catch (\Exception $e) {
                    // Not found product
                    $isValidated = false;
                }
            }
        }

        return $isValidated;
    }
}
