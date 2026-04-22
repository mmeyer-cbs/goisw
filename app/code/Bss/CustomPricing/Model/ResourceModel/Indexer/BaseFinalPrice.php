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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

use Bss\CustomPricing\Model\ResourceModel\ProductPrice;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\DB\Sql\Expression;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Prepare base select for Product Price index limited by specified dimensions: website and customer group
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BaseFinalPrice
{
    const BSS_INDEX_TABLE_NAME = "bss_custom_pricing_index";

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $modHelper;

    /**
     * @var Price\JoinAttributeProcessor
     */
    protected $customJoinAttrProcessor;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param Price\JoinAttributeProcessor $customJoinAttrProcessor
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Bss\CustomPricing\Helper\Data $modHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        JoinAttributeProcessor $joinAttributeProcessor,
        Price\JoinAttributeProcessor $customJoinAttrProcessor,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Bss\CustomPricing\Helper\Data $modHelper,
        $connectionName = 'indexer'
    ) {
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
        $this->moduleManager = $moduleManager;
        $this->eventManager = $eventManager;
        $this->metadataPool = $metadataPool;
        $this->modHelper = $modHelper;
        $this->customJoinAttrProcessor = $customJoinAttrProcessor;
    }

    /**
     * Build query for base final price.
     *
     * @param array $changedData struct exam ["type_id" => "simple", "changed_product_ids" => [1,2,3], "rule_id" => 1]
     * @return Select|bool
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getQuery($changedData)
    {
        $ruleId = $changedData["rule_id"];
        $productType = $changedData["type_id"];
        $entityIds = $changedData["changed_product_ids"];

        $connection = $this->getConnection();
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $customerGroupExpr = $connection->getCheckSql(
            'rules.is_not_logged_rule = 1',
            'cg.customer_group_id = customers.group_id OR cg.customer_group_id = 0',
            'cg.customer_group_id = customers.group_id'
        );

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['product_id' => 'entity_id']
        )->joinLeft(
            ['rules' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\PriceRule::TABLE)],
            'rules.id = ' . $ruleId,
            ['rule_id' => 'id']
        )->joinLeft(
            ['ac' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\AppliedCustomers::TABLE)],
            "ac.rule_id = rules.id",
            []
        )->joinLeft(
            ['customers' => $this->getTable("customer_entity")],
            'customers.entity_id = ac.customer_id',
            []
        )->joinInner(
            ['cg' => $this->getTable('customer_group')],
            $customerGroupExpr,
            ['customer_group_id']
        )->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id',
            ['pw.website_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id AND rules.website_id = pw.website_id',
            []
        )->joinLeft(
        // we need this only for BCC in case someone expects table `tp` to be present in query
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND' .
            ' tp.customer_group_id = cg.customer_group_id AND tp.website_id = pw.website_id',
            []
        );

        $this->getTierPriceTableJoin($select, $linkField);

        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->joinAttributeProcessor->process($select, 'tax_class_id');
        } else {
            $taxClassId = new \Zend_Db_Expr(0);
        }
        $select->columns(['tax_class_id' => $taxClassId]);

        // only reindex enable product
        // $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);

        $price = $this->getCustomPriceExpr($select);

        $leastSqlData = [$price];
        $tierPriceExpr = $this->getTierPriceExpr($connection, $price);
        if ($tierPriceExpr) {
            $leastSqlData[] = $tierPriceExpr['if_null'];
        }

        if ($specialPriceExpr = $this->getSpecialPriceExpr($select)) {
            $leastSqlData[] = $specialPriceExpr;
        }
        if (count($leastSqlData) > 1) {
            $finalPrice = $connection->getLeastSql($leastSqlData);
        } else {
            $finalPrice = $price;
        }

        $select->columns(
            [
                //custom price in bss_custom_price
                'price' => $price,
                'final_price' => $connection->getIfNullSql($finalPrice, 0),
                'min_price' => $connection->getIfNullSql($finalPrice, 0),
                'max_price' => $connection->getIfNullSql($finalPrice, 0),
                'tier_price' => $tierPriceExpr ?
                    $tierPriceExpr["total_tier_price"] :
                    new \Zend_Db_Expr('NULL'),
            ]
        );

        // reindex product type
        $select->where("e.type_id = ?", $productType);

        if ($entityIds !== null) {
            $select->where(sprintf('e.entity_id BETWEEN %s AND %s', min($entityIds), max($entityIds)));
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Throw event for backward compatibility
         */
        $this->eventManager->dispatch(
            'prepare_bss_custom_pricing_product_index_select',
            [
                'select' => $select,
                'entity_field' => new ColumnValueExpression('e.entity_id'),
                'website_field' => new ColumnValueExpression('pw.website_id'),
                'store_field' => new ColumnValueExpression('cwd.default_store_id'),
            ]
        );

        return $select->distinct(true);
    }

    /**
     * Join tier price table
     *
     * @param Select $select
     * @param string $linkField
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTierPriceTableJoin(Select $select, $linkField)
    {
        if ($this->modHelper->applyNormalTierPrice()) {
            $select->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `Specific Customer Group`
                ['tier_price_1' => $this->getTable('catalog_product_entity_tier_price')],
                'tier_price_1.' . $linkField . ' = e.' . $linkField . ' AND tier_price_1.all_groups = 0' .
                ' AND tier_price_1.customer_group_id = cg.customer_group_id AND tier_price_1.qty = 1' .
                ' AND tier_price_1.website_id = 0',
                []
            )->joinLeft(
            // calculate tier price specified as Website = `Specific Website`
            //and Customer Group = `Specific Customer Group`
                ['tier_price_2' => $this->getTable('catalog_product_entity_tier_price')],
                'tier_price_2.' . $linkField . ' = e.' . $linkField . ' AND tier_price_2.all_groups = 0 ' .
                'AND tier_price_2.customer_group_id = cg.customer_group_id AND tier_price_2.qty = 1' .
                ' AND tier_price_2.website_id = pw.website_id',
                []
            )->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `ALL GROUPS`
                ['tier_price_3' => $this->getTable('catalog_product_entity_tier_price')],
                'tier_price_3.' . $linkField . ' = e.' . $linkField . ' AND tier_price_3.all_groups = 1 ' .
                'AND tier_price_3.customer_group_id = 0 AND tier_price_3.qty = 1 AND tier_price_3.website_id = 0',
                []
            )->joinLeft(
            // calculate tier price specified as Website = `Specific Website` and Customer Group = `ALL GROUPS`
                ['tier_price_4' => $this->getTable('catalog_product_entity_tier_price')],
                'tier_price_4.' . $linkField . ' = e.' . $linkField . ' AND tier_price_4.all_groups = 1' .
                ' AND tier_price_4.customer_group_id = 0 AND tier_price_4.qty = 1' .
                ' AND tier_price_4.website_id = pw.website_id',
                []
            );
        }
    }

    /**
     * Get tier price expression
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Zend_Db_Expr $price
     * @return false|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTierPriceExpr($connection, $price)
    {
        if ($this->modHelper->applyNormalTierPrice()) {
            $tierPrice = $this->getTotalTierPriceExpression($price);
            $maxUnsignedBigint = '~0';
            // Return tier price expression
            return [
                'if_null' => $connection->getIfNullSql($tierPrice, $maxUnsignedBigint),
                'total_tier_price' => $tierPrice
            ];
        }
        return false;
    }

    /**
     * Get total tier price expression
     *
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTotalTierPriceExpression(\Zend_Db_Expr $priceExpression)
    {
        $maxUnsignedBigint = '~0';

        return $this->getConnection()->getCheckSql(
            implode(
                ' AND ',
                [
                    'tier_price_1.value_id is NULL',
                    'tier_price_2.value_id is NULL',
                    'tier_price_3.value_id is NULL',
                    'tier_price_4.value_id is NULL'
                ]
            ),
            'NULL',
            $this->getConnection()->getLeastSql(
                [
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_1', $priceExpression),
                        $maxUnsignedBigint
                    ),
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_2', $priceExpression),
                        $maxUnsignedBigint
                    ),
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_3', $priceExpression),
                        $maxUnsignedBigint
                    ),
                    $this->getConnection()->getIfNullSql(
                        $this->getTierPriceExpressionForTable('tier_price_4', $priceExpression),
                        $maxUnsignedBigint
                    ),
                ]
            )
        );
    }

    /**
     * Get tier price expression for table
     *
     * @param string $tableAlias
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTierPriceExpressionForTable($tableAlias, \Zend_Db_Expr $priceExpression): \Zend_Db_Expr
    {
        return $this->getConnection()->getCheckSql(
            sprintf('%s.value = 0', $tableAlias),
            sprintf(
                'ROUND(%s * (1 - ROUND(%s.percentage_value * cwd.rate, 4) / 100), 4)',
                $priceExpression,
                $tableAlias
            ),
            sprintf('ROUND(%s.value * cwd.rate, 4)', $tableAlias)
        );
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    public function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }

    /**
     * Get custom price column
     *
     * @param Select $select
     * @return \Zend_Db_Expr
     * @throws \Exception
     */
    private function getCustomPriceExpr(Select $select)
    {
        $connection = $this->resource->getConnection($this->connectionName);
        $joinType = "joinLeft";
        $productIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $dAlias = 'tad_custom_price';

        $select->{$joinType}(
            [$dAlias => $this->getTable(ProductPrice::TABLE)],
            "{$dAlias}.product_id = e.{$productIdField} AND {$dAlias}.rule_id = rules.id",
            []
        );

        return $connection->getIfNullSql("{$dAlias}.custom_price", 0);
    }

    /**
     * Get special price expression
     *
     * @param Select $select
     * @param string|null $linkAlias
     * @param string|null $linkField
     * @return false|\Zend_Db_Expr
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function getSpecialPriceExpr(Select $select, $linkAlias = null, $linkField = "product_id")
    {
        if ($this->modHelper->applyNormalSpecialPrice()) {
            $connection = $this->getConnection();
            if ($linkAlias && $linkField) {
                $customLinkData = [
                    'link_alias' => $linkAlias,
                    'link_field' => $linkField
                ];
                $specialPrice = $this->customJoinAttrProcessor->process($select, $customLinkData, 'special_price');
                $specialFrom = $this->customJoinAttrProcessor->process($select, $customLinkData, 'special_from_date');
                $specialTo = $this->customJoinAttrProcessor->process($select, $customLinkData, 'special_to_date');
            } else {
                $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
                $specialFrom = $this->joinAttributeProcessor->process($select, 'special_from_date');
                $specialTo = $this->joinAttributeProcessor->process($select, 'special_to_date');
            }
            $currentDate = 'cwd.website_date';

            $maxUnsignedBigint = '~0';
            $specialFromDate = $connection->getDatePartSql($specialFrom);
            $specialToDate = $connection->getDatePartSql($specialTo);
            $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
            $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
            // return special price expression
            return $connection->getCheckSql(
                "{$specialPrice} IS NOT NULL AND ({$specialFromExpr}) AND ({$specialToExpr})",
                $specialPrice,
                $maxUnsignedBigint
            );
        }
        return false;
    }

    /**
     * Get price from bss_custom_pricing_index
     *
     * @param string|array $ruleIds
     * @param int $productId
     * @param int $customerGroup
     * @return array|false
     */
    public function getPriceFromIndex($ruleIds, $productId, $customerGroup)
    {
        $connection = $this->getConnection();
        if (!is_array($ruleIds)) {
            if ($ruleIds !== null) {
                $ruleIds = explode("-", $ruleIds);
            } else {
                $ruleIds = [];
            }
        }
        if (!$customerGroup) {
            $customerGroup = 0;
        }
        if (!empty($ruleIds) && is_array($ruleIds) && $productId) {
            $customPrice = [];
            foreach ($ruleIds as $ruleId) {
                $select = $connection->select()
                    ->from(
                        $this->getTable(self::BSS_INDEX_TABLE_NAME)
                    )->where(
                        sprintf(
                            'product_id = %s AND rule_id = %s AND customer_group_id = %s',
                            $productId,
                            $ruleId,
                            $customerGroup
                        )
                    );
                $data = $connection->fetchAll($select);
                if (!empty($data)) {
                    $customPrice[] = $data[0];
                }
            }
            return $customPrice;
        }
        return false;
    }
}
