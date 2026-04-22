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
 * @package    Bss_
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

/**
 * Grouped product price indexer
 */
class GroupedPriceIndexer extends AbstractPriceIndexer implements ProductTypeIndexerInterface
{
    /**
     * Execute indexing
     *
     * @param \Magento\Framework\Indexer\Dimension[] $dimensions
     * @param array $changedData Struct exam ["type_id" => "simple", "changed_product_ids" => [1,2,3], "rule_id" => 1]
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeIndex($dimensions, $changedData)
    {
        $query = $this->prepareGroupedProductPriceDataSelect($dimensions, $changedData)
            ->insertFromSelect($this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME));
        $this->getConnection()->query($query);
    }

    /**
     * Prepare data index select for Grouped products prices
     *
     * @param array $dimensions
     * @param array $changedData
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    private function prepareGroupedProductPriceDataSelect($dimensions, $changedData)
    {
        $entityIds = $changedData["changed_product_ids"];
        $ruleId = $changedData["rule_id"];

        $select = $this->getConnection()->select();

        $customerGroupExpr = $this->getConnection()->getCheckSql(
            'rules.is_not_logged_rule = 1',
            '(cg.customer_group_id = c.group_id OR cg.customer_group_id = 0)',
            'cg.customer_group_id = c.group_id'
        );

        $select->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['product_id' => 'entity_id']
        )->join(
            ['rules' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\PriceRule::TABLE)],
            sprintf("rules.id = %s", $ruleId),
            []
        )->joinLeft(
            ['ac' => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\AppliedCustomers::TABLE)],
            'ac.rule_id = rules.id',
            []
        )->joinLeft(
            ['c' => $this->getTable('customer_entity')],
            'c.entity_id = ac.customer_id',
            []
        )->join(
            ['cg' => $this->getTable("customer_group")],
            $customerGroupExpr,
            []
        );

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select->joinLeft(
            ['l' => $this->getTable('catalog_product_link')],
            'e.' . $linkField . ' = l.product_id AND l.link_type_id=' . Link::LINK_TYPE_GROUPED,
            []
        );
        //additional information about inner products
        $select->joinLeft(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.linked_product_id',
            []
        );
        $connection = $this->getConnection();
        $taxClassId = $connection->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)');
        $minCheckSql = $connection->getCheckSql(
            "le.required_options = 0",
            $connection->getCheckSql(
                'bss_idx.min_price IS NOT NULL',
                "bss_idx.min_price",
                "i.min_price"
            ),
            0
        );
        $maxCheckSql = $connection->getCheckSql(
            "le.required_options = 0",
            $connection->getCheckSql(
                'bss_idx.max_price IS NOT NULL',
                "bss_idx.max_price",
                "i.max_price"
            ),
            0
        );
        $select->joinLeft(
            ['bss_idx' => $this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME)],
            'bss_idx.product_id = l.linked_product_id AND bss_idx.rule_id = ' . $ruleId,
            ['rule_id']
        );
        $select->columns(
            [
                'cg.customer_group_id',
                'i.website_id',
            ]
        )->join(
            ['i' => $this->getCoreIdxTable($dimensions)],
            'i.entity_id = l.linked_product_id',
            [
                'tax_class_id' => $taxClassId,
                'price' => new \Zend_Db_Expr('NULL'),
                'final_price' => new \Zend_Db_Expr('NULL'),
                'min_price' => new \Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                'max_price' => new \Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                'tier_price' => new \Zend_Db_Expr('NULL'),
            ]
        );
        $select->group(
            ['e.entity_id', 'cg.customer_group_id', 'i.website_id']
        );
        $select->where(
            'e.type_id=?',
            GroupedType::TYPE_CODE
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }
}
