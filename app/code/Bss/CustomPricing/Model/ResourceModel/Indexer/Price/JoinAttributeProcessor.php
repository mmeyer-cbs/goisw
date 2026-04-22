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

namespace Bss\CustomPricing\Model\ResourceModel\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Allows to join product attribute to Select. Used for build price index for specified dimension
 */
class JoinAttributeProcessor
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ResourceConnection $resource,
        $connectionName = 'indexer'
    ) {
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->connectionName = $connectionName;
    }

    /**
     * Processing join attribute
     *
     * @param Select $select
     * @param array $customLinkData exam ['link_alias' => 'i','link_field' => 'product_id']
     * @param string $attributeCode
     * @param string|null $attributeValue
     * @return \Zend_Db_Expr
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(Select $select, $customLinkData, $attributeCode, $attributeValue = null): \Zend_Db_Expr
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $attributeId = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $connection = $this->resource->getConnection($this->connectionName);
        $joinType = $attributeValue !== null ? 'join' : 'joinLeft';
        $productIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $linkProductField = $customLinkData ?
            "$customLinkData[link_alias].$customLinkData[link_field]" :
            "e.{$productIdField}";
        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attributeCode;
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = {$linkProductField} AND {$alias}.attribute_id = {$attributeId}" .
                " AND {$alias}.store_id = 0",
                []
            );
            $whereExpression = new Expression("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attributeCode;
            $sAlias = 'tas_' . $attributeCode;

            $select->{$joinType}(
                [$dAlias => $attributeTable],
                "{$dAlias}.{$productIdField} = {$linkProductField} AND {$dAlias}.attribute_id = {$attributeId}" .
                " AND {$dAlias}.store_id = 0",
                []
            );
            $select->joinLeft(
                [$sAlias => $attributeTable],
                "{$sAlias}.{$productIdField} = {$linkProductField} AND {$sAlias}.attribute_id = {$attributeId}" .
                " AND {$sAlias}.store_id = cwd.default_store_id",
                []
            );
            $whereExpression = $connection->getCheckSql(
                $connection->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        if ($attributeValue !== null) {
            $select->where("{$whereExpression} = ?", $attributeValue);
        }

        return $whereExpression;
    }
}
