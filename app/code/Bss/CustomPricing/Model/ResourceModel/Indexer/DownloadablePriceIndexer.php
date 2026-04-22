<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

use Bss\CustomPricing\Helper\Data as ModuleHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;

/**
 * Downloadable Product Price Indexer Resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadablePriceIndexer extends AbstractPriceIndexer implements ProductTypeIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var Config
     */
    private $eavConfig;

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
        BaseFinalPrice $baseFinalPrice,
        Config $eavConfig,
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
        $this->baseFinalPrice = $baseFinalPrice;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritDoc
     */
    public function executeIndex($dimensions, $changedData)
    {
        $this->fillFinalPrice($changedData);
        $this->basePriceModifier->modifyPrice($this->getMainTable(), $changedData);
        $this->applyDownloadableLink($dimensions, $changedData);
    }

    /**
     * Calculate and apply Downloadable links price to index
     *
     * @param array $dimensions
     * @param array $changedData
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function applyDownloadableLink(array $dimensions, $changedData)
    {
        $temporaryDownloadableTableName = 'bcp_catalog_product_index_price_downlod_temp';
        $this->getConnection()->createTemporaryTableLike(
            $temporaryDownloadableTableName,
            $this->getTable('catalog_product_index_price_downlod_tmp'),
            true
        );
        $this->fillTemporaryTable($temporaryDownloadableTableName, $changedData);
        $this->updateTemporaryDownloadableTable($this->getMainTable(), $temporaryDownloadableTableName, $changedData);
        $this->getConnection()->delete($temporaryDownloadableTableName);
        return $this;
    }

    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttribute($attributeCode)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
    }

    /**
     * Put data into catalog product price indexer Downloadable links price  temp table
     *
     * @param string $temporaryDownloadableTableName
     * @param array $changedData
     * @return void
     * @throws \Exception
     */
    private function fillTemporaryTable(string $temporaryDownloadableTableName, array $changedData)
    {
        $ruleId = $changedData["rule_id"];
        $dlType = $this->getAttribute('links_purchased_separately');
        $ifPrice = $this->getConnection()->getIfNullSql('dlpw.price_id', 'dlpd.price');
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['i' => $this->getMainTable()],
            ['entity_id' => 'product_id', 'customer_group_id', 'website_id']
        )->join(
            ['dl' => $dlType->getBackend()->getTable()],
            "dl.{$linkField} = i.product_id AND dl.attribute_id = {$dlType->getAttributeId()}" . " AND dl.store_id = 0",
            []
        )->join(
            ['dll' => $this->getTable('downloadable_link')],
            'dll.product_id = i.product_id',
            []
        )->join(
            ['dlpd' => $this->getTable('downloadable_link_price')],
            'dll.link_id = dlpd.link_id AND dlpd.website_id = 0',
            []
        )->joinLeft(
            ['dlpw' => $this->getTable('downloadable_link_price')],
            'dlpd.link_id = dlpw.link_id AND dlpw.website_id = i.website_id',
            []
        )->where(
            'dl.value = ?',
            1
        )->where(
            'i.rule_id = ?',
            $ruleId
        )->group(
            ['i.product_id', 'i.customer_group_id', 'i.website_id']
        )->columns(
            [
                'min_price' => new \Zend_Db_Expr('MIN(' . $ifPrice . ')'),
                'max_price' => new \Zend_Db_Expr('SUM(' . $ifPrice . ')'),
            ]
        );
        $query = $select->insertFromSelect($temporaryDownloadableTableName);
        $this->getConnection()->query($query);
    }

    /**
     * Update data in the catalog product price indexer temp table
     *
     * @param string $bssIdxTblName
     * @param string $temporaryDownloadableTableName
     * @param array
     * @return void
     */
    private function updateTemporaryDownloadableTable(
        string $bssIdxTblName,
        string $temporaryDownloadableTableName,
        $changedData
    ) {
        $ruleId = $changedData["rule_id"];
        $connection = $this->getConnection();
        $ifTierPrice = $connection->getCheckSql(
            'i.tier_price IS NOT NULL',
            '(i.tier_price + id.min_price)',
            'NULL'
        );
        $whereCond = 'i.product_id = id.entity_id AND i.customer_group_id = id.customer_group_id' .
            " AND i.website_id = id.website_id AND i.rule_id = $ruleId";

        $selectForCrossUpdate = $connection->select()->join(
            ['id' => $temporaryDownloadableTableName],
            $whereCond,
            []
        );
        // adds price of custom option, that was applied in DefaultPrice::_applyCustomOption
        $selectForCrossUpdate->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price + id.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + id.max_price'),
                'tier_price' => new \Zend_Db_Expr($ifTierPrice),
            ]
        );
        $query = $selectForCrossUpdate->crossUpdateFromSelect(['i' => $bssIdxTblName]);
        $connection->query($query);
    }

    /**
     * Fill final price
     *
     * @param array $changedData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function fillFinalPrice($changedData)
    {
        $select = $this->baseFinalPrice->getQuery($changedData);
        $query = $select->insertFromSelect($this->getMainTable(), [], false);
        $this->getConnection()->query($query);
    }
}
