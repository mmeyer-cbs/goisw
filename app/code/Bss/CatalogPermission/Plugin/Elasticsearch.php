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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Plugin;

use Bss\CatalogPermission\Helper\ModuleConfig;
use Bss\CatalogPermission\Model\Category;
use Bss\CatalogPermission\Model\GetProductIdQuery;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Elasticsearch
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var GetProductIdQuery
     */
    protected $productQuery;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Category $category
     * @param ModuleConfig $moduleConfig
     * @param GetProductIdQuery $productQuery
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        \Bss\CatalogPermission\Model\Category $category,
        \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig,
        \Bss\CatalogPermission\Model\GetProductIdQuery $productQuery
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->category = $category;
        $this->moduleConfig = $moduleConfig;
        $this->productQuery = $productQuery;
    }

    /**
     * Exclude product from category banned
     *
     * @param \Magento\Elasticsearch7\Model\Client\Elasticsearch $subject
     * @param array $query
     * @return array[]
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeQuery(\Magento\Elasticsearch7\Model\Client\Elasticsearch $subject, $query)
    {
        if ($this->moduleConfig->enableCatalogPermission()
            && $this->moduleConfig->enableProductRestricted()
        ) {
            $productIds = $this->productQuery->getProductIds();
            if ($productIds) {
                $productIdsExcl = [];
                foreach ($productIds as $productId) {
                    $productIdsExcl[] = $productId['product_id'];
                }
                $ids = $this->mergeIds($query, $productIdsExcl);
                $query['body']['query']['bool']['must_not'] = [
                    'ids' => [ 'values' => $ids]
                ];
            }
        }
        return [$query];
    }

    /**
     * Merge ids
     *
     * @param array $query
     * @param array $ids
     * @return array
     */
    public function mergeIds($query, $ids)
    {
        try {
            if (isset($query['body']['query']['bool']["must_not"])) {
                $idsOld = $query['body']['query']['bool']["must_not"];
                $ids = array_merge($ids, $idsOld);
                return array_unique($ids);
            }
        } catch (\Exception $e) {
            return $ids;
        }
        return $ids;
    }
}
