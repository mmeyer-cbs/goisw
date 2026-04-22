<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

use Bss\CustomPricing\Helper\Data as ModuleHelper;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\ScopeInterface;

/**
 * Configurable Products Price Indexer Resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurablePriceIndexer extends AbstractPriceIndexer implements ProductTypeIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @inheritDoc
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        ModuleHelper $helper,
        JoinAttributeProcessor $joinAttributeProcessor,
        BasePriceModifier $basePriceModifier,
        TableMaintainer $tableMaintainer,
        ScopeConfigInterface $scopeConfig,
        BaseFinalPrice $baseFinalPrice,
        $connectionName = 'indexer'
    ) {
        parent::__construct(
            $metadataPool,
            $resource,
            $helper,
            $joinAttributeProcessor,
            $basePriceModifier,
            $tableMaintainer,
            $connectionName
        );
        $this->scopeConfig = $scopeConfig;
        $this->baseFinalPrice = $baseFinalPrice;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function executeIndex($dimensions, $changedData)
    {
        $select = $this->baseFinalPrice->getQuery($changedData);
        $query = $select->insertFromSelect($this->getMainTable(), [], true);
        $this->tableMaintainer->getConnection()->query($query);

        $this->basePriceModifier->modifyPrice($this->getMainTable(), $changedData);
        $this->applyConfigurableOption($dimensions, $changedData);
    }

    /**
     * Apply configurable option
     *
     * @param array $dimensions
     * @param array $changedData
     *
     * @return $this
     * @throws \Exception
     */
    private function applyConfigurableOption(
        array $dimensions,
        array $changedData
    ) {
        $temporaryOptionsTableName = 'bcp_catalog_product_index_price_cfg_opt_temp';
        $this->getConnection()->createTemporaryTableLike(
            $temporaryOptionsTableName,
            $this->getTable('catalog_product_index_price_cfg_opt_tmp'),
            true
        );

        $this->fillTemporaryOptionsTable($temporaryOptionsTableName, $dimensions, $changedData);
        $this->updateTemporaryTable($this->getMainTable(), $temporaryOptionsTableName, $changedData);

        $this->getConnection()->delete($temporaryOptionsTableName);

        return $this;
    }

    /**
     * Put data into catalog product price indexer config option temp table
     *
     * @param string $temporaryOptionsTableName
     * @param array $dimensions
     * @param array $changedData
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function fillTemporaryOptionsTable(string $temporaryOptionsTableName, array $dimensions, array $changedData)
    {
        $entityIds = $changedData["changed_product_ids"];
        $ruleId = $changedData["rule_id"];

        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $connection = $this->getConnection();

        $customerGroupExpr = $connection->getCheckSql(
            'rules.is_not_logged_rule = 1',
            '(cg.customer_group_id = ce.group_id OR cg.customer_group_id = 0)',
            'cg.customer_group_id = ce.group_id'
        ) . " AND cg.customer_group_id = i.customer_group_id";

        $select = $connection->select()->from(
            ['i' => $this->getCoreIdxTable($dimensions)],
            []
        )->join(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.product_id = i.entity_id',
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.' . $linkField . ' = l.parent_id',
            []
        )->join(
            ['rules' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\PriceRule::TABLE)],
            sprintf("rules.id = %s", $ruleId),
            []
        )->joinLeft(
            ['bss_ac' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\AppliedCustomers::TABLE)],
            "bss_ac.rule_id = rules.id",
            []
        )->joinLeft(
            ['ce' => $this->getTable("customer_entity")],
            'ce.entity_id = bss_ac.customer_id',
            []
        )->join(
            ['cg' => $this->getTable("customer_group")],
            $customerGroupExpr,
            []
        )->joinLeft(
            ['bss_idx' => $this->getMainTable()],
            "bss_idx.product_id = l.product_id AND bss_idx.rule_id = rules.id",
            []
        );

        // Does not make sense to extend query if out of stock products won't appear in tables for indexing
        if ($this->isConfigShowOutOfStock()) {
            $select->join(
                ['si' => $this->getTable('cataloginventory_stock_item')],
                'si.product_id = l.product_id',
                []
            );
            $select->where('si.is_in_stock = ?', Stock::STOCK_IN_STOCK);
        }

        // Tier price module check
        $tierPriceExpr = new \Zend_Db_Expr("NULL");
        if ($this->helper->applyNormalTierPrice()) {
            $tierPriceExpr = $this->getModulePriceExpr("tier_price", "i", "bss_idx");
        }

        // Special price module check
        $finalField = "price";
        if ($this->helper->applyNormalSpecialPrice()) {
            // if apply normal special price and default tier price then will use 'final_price' field
            if ($this->helper->applyNormalTierPrice()) {
                $finalField = "final_price";
            } else {
                // if not (default special is apply) we will get 'expression of special price'
                // then get least of 'price' field and 'special price expression'
                $select->joinInner(
                    ['pw' => $this->getTable('catalog_product_website')],
                    'pw.product_id = le.entity_id',
                    []
                )->joinInner(
                    ['cwd' => $this->getTable('catalog_product_index_website')],
                    'pw.website_id = cwd.website_id',
                    []
                );
                $specialPriceExpr = $this->baseFinalPrice
                    ->getSpecialPriceExpr($select, "l");
            }
        }
        $finalPriceExpr = $this->getModulePriceExpr($finalField, "i", "bss_idx");

        $select->columns(
            [
                'le.entity_id',
                'cg.customer_group_id',
                'i.website_id',
                'min_price' => isset($specialPriceExpr) ?
                    $connection->getLeastSql(["MIN($finalPriceExpr)", "MIN($specialPriceExpr)"]) :
                    "MIN($finalPriceExpr)",
                'max_price' => isset($specialPriceExpr) ?
                    $connection->getLeastSql(["MAX($finalPriceExpr)", "MAX($specialPriceExpr)"]) :
                    "MAX($finalPriceExpr)",
                'tier_price' => "MIN($tierPriceExpr)"
            ]
        )->group(
            ['le.entity_id', 'cg.customer_group_id', 'i.website_id']
        );
        if ($entityIds !== null) {
            $select->where('le.entity_id IN (?)', $entityIds);
        }

        $query = $select->insertFromSelect($temporaryOptionsTableName);
        $this->getConnection()->query($query);
    }

    /**
     * Get price expression
     *
     * @param string $field
     * @param string $baseAlias
     * @param string $moduleAlias
     * @return \Zend_Db_Expr
     */
    private function getModulePriceExpr($field, $baseAlias, $moduleAlias)
    {
        $baseAlias .= ".";
        $moduleAlias .= ".";

        $connection = $this->getConnection();
        return $connection->getCheckSql(
            $moduleAlias . $field . " IS NOT NULL",
            $moduleAlias . $field,
            $baseAlias . $field
        );
    }

    /**
     * Update data in the catalog product price indexer temp table
     *
     * @param string $temporaryPriceTableName
     * @param string $temporaryOptionsTableName
     * @param array $changedData
     *
     * @return void
     */
    private function updateTemporaryTable(
        string $temporaryPriceTableName,
        string $temporaryOptionsTableName,
        array $changedData
    ) {
        $table = ['i' => $temporaryPriceTableName];
        $where = 'i.product_id = io.entity_id ' .
            'AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id' .
            ' AND i.rule_id = ' . $changedData['rule_id'];
        $selectForCrossUpdate = $this->getConnection()->select()->join(
            ['io' => $temporaryOptionsTableName],
            $where,
            []
        );
        $selectForCrossUpdate->where($where);
        // adds price of custom option, that was applied in DefaultPrice::_applyCustomOption
        $selectForCrossUpdate->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price - i.price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price - i.price + io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $selectForCrossUpdate->crossUpdateFromSelect($table);
        $this->getConnection()->query($query);
    }

    /**
     * Is flag Show Out Of Stock setted
     *
     * @return bool
     */
    private function isConfigShowOutOfStock(): bool
    {
        return $this->scopeConfig->isSetFlag(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
    }
}
