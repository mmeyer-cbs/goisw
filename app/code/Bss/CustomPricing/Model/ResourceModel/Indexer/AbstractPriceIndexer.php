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

use Bss\CustomPricing\Helper\Data as ModuleHelper;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer as CoreTableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\Dimension;

/**
 * Product type price indexer abstraction
 */
abstract class AbstractPriceIndexer
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ModuleHelper
     */
    protected $helper;

    /**
     * @var CoreTableMaintainer
     */
    protected $tableMaintainer;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var JoinAttributeProcessor
     */
    protected $joinAttributeProcessor;

    /**
     * @var BasePriceModifier
     */
    protected $basePriceModifier;

    /**
     * AbstractPriceIndexer constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param ModuleHelper $helper
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param BasePriceModifier $basePriceModifier
     * @param CoreTableMaintainer $tableMaintainer
     * @param string $connectionName
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        ModuleHelper $helper,
        JoinAttributeProcessor $joinAttributeProcessor,
        BasePriceModifier $basePriceModifier,
        CoreTableMaintainer $tableMaintainer,
        $connectionName = 'indexer'
    ) {
        $this->connectionName = $connectionName;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->tableMaintainer = $tableMaintainer;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
        $this->basePriceModifier = $basePriceModifier;
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     * @throws \DomainException
     */
    protected function getConnection(): AdapterInterface
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
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }

    /**
     * Get core main price index table
     *
     * @param array $dimensions
     * @return string
     */
    protected function getCoreIdxTable($dimensions)
    {
        return $this->tableMaintainer->getMainTable($dimensions);
    }

    /**
     * Get main table
     *
     * @return string
     */
    protected function getMainTable()
    {
        return $this->getTable(BaseFinalPrice::BSS_INDEX_TABLE_NAME);
    }
}
