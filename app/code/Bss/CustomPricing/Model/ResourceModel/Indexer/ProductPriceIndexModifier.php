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

namespace Bss\CustomPricing\Model\ResourceModel\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;

/**
 * Class for adding catalog rule prices to price index table.
 */
class ProductPriceIndexModifier implements PriceModifierInterface
{
    /**
     * @var Price
     */
    private $priceResourceModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @param Price $priceResourceModel
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     */
    public function __construct(
        Price $priceResourceModel,
        ResourceConnection $resourceConnection,
        $connectionName = 'indexer'
    ) {
        $this->priceResourceModel = $priceResourceModel;
        $this->resourceConnection = $resourceConnection;
        $this->connectionName = $connectionName;
    }

    /**
     * @inheritdoc
     */
    public function modifyPrice(IndexTableStructure $ixTblName, array $changedData = []) : void
    {
        $entityIds = $changedData["changed_product_ids"];
        $connection = $this->resourceConnection->getConnection($this->connectionName);

        $select = $connection->select();

        $select->join(
            ['cpiw' => $this->priceResourceModel->getTable('catalog_product_index_website')],
            'cpiw.website_id = i.website_id',
            []
        );
        $select->join(
            ['cpp' => $this->priceResourceModel->getMainTable()],
            'cpp.product_id = i.product_id'
            . ' AND cpp.customer_group_id = i.customer_group_id'
            . ' AND cpp.website_id = i.website_id'
            . ' AND cpp.rule_date = cpiw.website_date',
            []
        );
        if ($entityIds) {
            $select->where('i.product_id IN (?)', $entityIds);
        }

        $finalPrice = 'final_price';
        $finalPriceExpr = $select->getConnection()->getLeastSql([
            $finalPrice,
            $select->getConnection()->getIfNullSql('cpp.rule_price', 'i.' . $finalPrice),
        ]);
        $minPrice = 'min_price';
        $minPriceExpr = $select->getConnection()->getLeastSql([
            $minPrice,
            $select->getConnection()->getIfNullSql('cpp.rule_price', 'i.' . $minPrice),
        ]);
        $select->columns([
            $finalPrice => $finalPriceExpr,
            $minPrice => $minPriceExpr,
        ]);

        $query = $connection->updateFromSelect($select, ['i' => $ixTblName]);
        $connection->query($query);
    }
}
