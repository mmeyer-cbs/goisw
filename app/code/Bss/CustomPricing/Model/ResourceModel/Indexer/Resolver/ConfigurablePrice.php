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

namespace Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Class Customer group resolver
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConfigurablePrice
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @var IndexScopeResolverInterface|null
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory|null
     */
    private $dimensionFactory;

    /**
     * ConfigurablePrice constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param BaseSelectProcessorInterface $baseSelectProcessor
     * @param IndexScopeResolverInterface $priceTableResolver
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        BaseSelectProcessorInterface $baseSelectProcessor,
        IndexScopeResolverInterface $priceTableResolver,
        DimensionFactory $dimensionFactory
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->baseSelectProcessor = $baseSelectProcessor;
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * Get min price as low as frontend configurable product
     *
     * @param string $customerRule
     * @param int $parentProductId
     * @param int $storeId
     * @return false|array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMinPrice($customerRule, $parentProductId, $storeId)
    {
        $customerRule = str_replace("-", ",", $customerRule);
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productTable = $this->resource->getTableName('catalog_product_entity');
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $ifPrice = $this->resource->getConnection()->getIfNullSql('t.min_price', 'core_index.min_price');
        $priceSelect = $this->resource->getConnection()->select()
            ->from(['parent' => $productTable], ['last_min_price' => $ifPrice])
            ->joinInner(
                ['link' => $this->resource->getTableName('catalog_product_relation')],
                "link.parent_id = parent.$linkField",
                []
            )->joinInner(
                [BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS => $productTable],
                sprintf('%s.entity_id = link.child_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )->joinRight(
                [
                    'core_index' => $this->resource->getTableName('catalog_product_index_price')
                ],
                sprintf('%s.entity_id = core_index.entity_id ', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )->joinLeft(
                [
                    't' => $this->resource->getTableName('bss_custom_pricing_index')
                ],
                sprintf(
                    't.product_id = %s.entity_id AND t.rule_id IN (%s)',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS,
                    $customerRule
                ),
                []
            )->where('parent.entity_id = ?', $parentProductId)
            ->where('core_index.website_id = ?', $websiteId)
            ->where('core_index.customer_group_id = ?', $customerGroupId)
            ->order('last_min_price ' . Select::SQL_ASC)
            ->order(BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . '.' . $linkField . ' ' . Select::SQL_ASC)
            ->limit(1);
        $data = $this->resource->getConnection()->fetchCol($priceSelect);
        if (!empty($data)) {
            return $data[0];
        }
        return false;
    }
}
