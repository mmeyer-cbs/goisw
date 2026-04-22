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

use Magento\Framework\Indexer\Dimension;

/**
 * Simple product price indexer
 */
class SimplePriceIndexer implements ProductTypeIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    protected $baseFinalPrice;

    /**
     * @var BasePriceModifier
     */
    protected $basePriceModifier;

    /**
     * SimplePriceIndexer constructor.
     *
     * @param BaseFinalPrice $baseFinalPrice
     * @param BasePriceModifier $basePriceModifier
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        BasePriceModifier $basePriceModifier
    ) {
        $this->baseFinalPrice = $baseFinalPrice;
        $this->basePriceModifier = $basePriceModifier;
    }

    /**
     * Execute indexing
     *
     * @param Dimension[] $dimensions
     * @param array $changedData struct exam ["type_id" => "simple", "changed_product_ids" => [1,2,3], "rule_id" => 1]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function executeIndex($dimensions, $changedData)
    {
        $select = $this->baseFinalPrice->getQuery($changedData);

        $indexPriceTable = $this->baseFinalPrice->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME);

        $query = $select->insertFromSelect($indexPriceTable, [], true);

        $connection = $this->baseFinalPrice->getConnection();

        $connection->query($query);

        $this->basePriceModifier->modifyPrice(
            $indexPriceTable,
            $changedData
        );
    }
}
