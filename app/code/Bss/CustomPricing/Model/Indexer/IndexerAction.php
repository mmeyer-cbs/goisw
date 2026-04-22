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

namespace Bss\CustomPricing\Model\Indexer;

use Bss\CustomPricing\Model\ResourceModel\Indexer\BundlePriceIndexer;
use Bss\CustomPricing\Model\ResourceModel\Indexer\ConfigurablePriceIndexer;
use Bss\CustomPricing\Model\ResourceModel\Indexer\DownloadablePriceIndexer;
use Bss\CustomPricing\Model\ResourceModel\Indexer\GroupedPriceIndexer;
use Bss\CustomPricing\Model\ResourceModel\Indexer\ProductTypeIndexerInterface;
use Bss\CustomPricing\Model\ResourceModel\Indexer\SimplePriceIndexer;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Exception\InputException;
use Magento\Downloadable\Model\Product\Type as DownloadablePType;
use Bss\CustomPricing\Model\ResourceModel\Indexer\ProductTypeIndexerFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;

/**
 * Reindex Action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerAction
{
    const BSS_INDEX_TABLE_NAME = "bss_custom_pricing_index";

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory
     */
    protected $dimensionCollectionFactory;

    /**
     * @var ProductTypeIndexerFactory
     */
    protected $productTypeIndexerFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $indexers;

    /**
     * AbstractAction constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory
     * @param ProductTypeIndexerFactory $productTypeIndexerFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory,
        ProductTypeIndexerFactory $productTypeIndexerFactory
    ) {
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
        $this->productTypeIndexerFactory = $productTypeIndexerFactory;
        $this->initIndexers();
    }

    /**
     * Indexers initiation
     */
    private function initIndexers()
    {
        foreach ($this->getTypeIndexers() as $indexerType) {
            $indexer = $this->productTypeIndexerFactory->create($indexerType["class_name"]);
            $this->indexers[$indexerType["type_id"]] = $indexer;
        }
    }

    /**
     * Retrieve price indexers per product type
     *
     * @return array
     */
    public function getTypeIndexers(): array
    {
        return [
            ["type_id" => ProductType::TYPE_SIMPLE, "class_name" => SimplePriceIndexer::class],
            ["type_id" => GroupedProductType::TYPE_CODE, "class_name" => GroupedPriceIndexer::class],
            ["type_id" => BundleProductType::TYPE_CODE, "class_name" => BundlePriceIndexer::class],
            ["type_id" => ConfigurableType::TYPE_CODE, "class_name" => ConfigurablePriceIndexer::class],
            ["type_id" => DownloadablePType::TYPE_DOWNLOADABLE, "class_name" => DownloadablePriceIndexer::class]
        ];
    }

    /**
     * Reindex rows data
     *
     * @param array $changedIds
     * @param bool $byRule
     * @return void
     * @throws InputException
     */
    public function reindexRows($changedIds = [], $byRule = false)
    {
        $changedProductIds = $this->prepareProductIds($changedIds);
        $productsTypes = $this->getProductsTypes($changedIds);
        $parentProductsTypes = $this->getParentProductsTypes($changedProductIds["product_ids"]);

        $remappingParentProducts = [];
        foreach ($parentProductsTypes as $type) {
            $remappingParentProducts = array_merge_recursive($remappingParentProducts, array_values($type));
        }

        $changedProductIds['product_ids'] = array_merge(
            $changedProductIds['product_ids'],
            ...array_values($remappingParentProducts)
        );
        // $productsTypes = array_merge_recursive($productsTypes, $parentProductsTypes);
        $productsTypes = $this->mergeProductTypesRecursive($productsTypes, $parentProductsTypes);

        $this->deleteNullCustomPriceIndexedData();
        if ($changedProductIds["product_ids"] || $byRule) {
            $this->deleteIndexedData($changedProductIds, $byRule);
        }

        foreach ($productsTypes as $ruleId => $types) {
            foreach ($types as $productsType => $changedProductsIds) {
                $indexer = $this->getIndexer($productsType);
                foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
                    $indexer->executeIndex(
                        $dimensions,
                        [
                            "type_id" => $productsType,
                            'changed_product_ids' => $changedProductsIds,
                            'rule_id' => $ruleId
                        ]
                    );
                }
            }
        }
    }

    /**
     * Merge products types array data
     *
     * @return array
     */
    private function mergeProductTypesRecursive()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                    $base[$key] = $this->mergeProductTypesRecursive($base[$key], $value);
                } else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }

    /**
     * Get indexer by product type
     *
     * @param string $productTypeName
     * @return ProductTypeIndexerInterface
     * @throws InputException
     */
    public function getIndexer($productTypeName)
    {
        switch ($productTypeName) {
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL:
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
                return $this->indexers[ProductType::TYPE_SIMPLE];
            default:
                return $this->indexers[$productTypeName];
        }
    }

    /**
     * Prepare change product ids by module product price ids
     *
     * @param array $changeIds
     * @return array
     */
    private function prepareProductIds($changeIds = []): array
    {
        $select = $this->connection->select()
            ->from(
                ['bss_product_price' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\ProductPrice::TABLE)],
                ["product_id", "rule_id"]
            );
        if ($changeIds) {
            $select->where(
                "id IN (?)",
                $changeIds
            );
        }
//        $select->where('custom_price IS NOT NULL');
        $select->where("((type_id not in ('bundle', 'configurable', 'grouped') AND custom_price IS NOT NULL) OR type_id in ('bundle', 'configurable', 'grouped'))");
        $results = $this->connection->fetchAll($select);
        $changeProducts = [
            "product_ids" => [],
            "rule_ids" => []
        ];
        foreach ($results as $value) {
            $changeProducts['product_ids'][] = $value["product_id"];
            if (!in_array($value["rule_id"], $changeProducts['rule_ids'])) {
                $changeProducts['rule_ids'][] = $value["rule_id"];
            }
        }
        return $changeProducts;
    }

    /**
     * Get products types.
     *
     * @param array $changedIds
     * @return array
     */
    private function getProductsTypes(array $changedIds = []): array
    {
        $select = $this->connection->select()->from(
            $this->getTable(\Bss\CustomPricing\Model\ResourceModel\ProductPrice::TABLE),
            ['product_id', 'type_id', 'rule_id']
        );
        if ($changedIds) {
            $select->where("id IN (?)", $changedIds);
        }
        $select->where("((type_id not in ('bundle', 'configurable', 'grouped') AND custom_price IS NOT NULL) OR type_id in ('bundle', 'configurable', 'grouped'))");
        $results = $this->connection->fetchAll($select);

        $byType = [];
        foreach ($results as $item) {
            $productType = $item["type_id"];
            $ruleId = $item["rule_id"];
            $productId = $item['product_id'];
            $byType[$ruleId][$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * Description like the method name =)
     */
    protected function deleteNullCustomPriceIndexedData()
    {
        // remove the product which custom price set was null
        $select = $this->connection->select()
            ->from(
                ['i' => $this->getTable(self::BSS_INDEX_TABLE_NAME)],
                ['product_id']
            )->join(
                ['bpp' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\ProductPrice::TABLE)],
                'i.product_id = bpp.product_id AND i.rule_id = bpp.rule_id',
                []
            )->where("bpp.custom_price IS NULL AND bpp.type_id NOT IN('bundle', 'configurable', 'grouped')");

        $query = $select->deleteFromSelect('i');
        $this->connection->query($query);
    }

    /**
     * Delete indexed data
     *
     * @param array $dataIds
     * @param bool|string $deleteType
     */
    public function deleteIndexedData($dataIds, $deleteType = false): void
    {
        $productIds = $dataIds["product_ids"];
        $ruleIds = $dataIds["rule_ids"];
        $select = $this->connection->select()
            ->from(
                ['bss_index_price' => $this->getTable(self::BSS_INDEX_TABLE_NAME)],
                ['product_id']
            );
        if ($deleteType != "all") {
            $select->where("bss_index_price.rule_id IN(?)", $ruleIds);
            if ($deleteType === false) {
                $select->where("product_id IN(?)", $productIds);
            }
        }
        $query = $select->deleteFromSelect("bss_index_price");
        $this->connection->query($query);
    }

    /**
     * Get parent products types
     *
     * Used for add composite products to reindex if we have only simple products in changed ids set
     *
     * @param array $productsIds
     * @return array
     */
    private function getParentProductsTypes(array $productsIds): array
    {
        $connection = $this->connection;
        $subSelect = $connection->select()->from(
            ['sub_pp' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\ProductPrice::TABLE)],
            'rule_id'
        )->where('sub_pp.product_id IN(?)', $productsIds);

        $select = $connection->select()->from(
            ['l' => $this->getTable('catalog_product_relation')],
            ''
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.' . $this->getProductIdFieldName() . ' = l.parent_id',
            ['e.entity_id as parent_id', 'type_id']
        )->where(
            'l.child_id IN(?)',
            $productsIds
        );
        // Only get parent is apply in rule
        $select->join(
            ['bss_pp' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\ProductPrice::TABLE)],
            "l.parent_id = bss_pp.product_id",
            ['rule_id']
        )->where('bss_pp.rule_id IN(?)', $subSelect);

        $select->group(['e.entity_id', 'bss_pp.rule_id']);
        $results = $connection->fetchAll($select);


        $byType = [];
        foreach ($results as $item) {
            $productType = $item["type_id"];
            $ruleId = $item["rule_id"];
            $productId = $item['parent_id'];
            $byType[$ruleId][$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * Get product id field name
     *
     * @return string
     */
    protected function getProductIdFieldName()
    {
        $connection = $this->connection;
        $table = $this->getTable('catalog_product_entity');
        $indexList = $connection->getIndexList($table);
        return $indexList[$connection->getPrimaryKeyName($table)]['COLUMNS_LIST'][0];
    }

    /**
     * Get table name
     *
     * @param string $tblName
     * @return string
     */
    private function getTable($tblName): string
    {
        return $this->resource->getTableName($tblName);
    }
}
