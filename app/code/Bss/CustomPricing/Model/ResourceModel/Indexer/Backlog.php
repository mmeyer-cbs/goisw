<?php
declare(strict_types=1);
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

/**
 * Class Backlog - update the bss_custom_pricing_index_cl follow the customer changed
 */
class Backlog
{
    const BSS_INDEX_CHANGE_LOG_TABLE_NAME = "bss_custom_pricing_index_cl";

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var Resolver\IndexerResolver
     */
    private $indexerResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Backlog constructor.
     *
     * @param Resolver\IndexerResolver $indexerResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Resolver\IndexerResolver $indexerResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resource = $indexerResolver->getResource();
        $this->indexerResolver = $indexerResolver;
        $this->logger = $logger;
    }

    /**
     * Update the change log table for schedule type indexer follow the customer change
     *
     * @param int $ruleId
     */
    public function setBacklogForRule($ruleId)
    {
        try {
            $select = $this->getInitSelect();
            $select->where('product_price.rule_id = ?', $ruleId);
            $this->insertToBacklog($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Set list id to backlog
     *
     * @param array $ids
     */
    public function setBacklog(array $ids)
    {
        try {
            $select = $this->getInitSelect();
            $select->where('product_price.id IN (?)', $ids);
            $this->insertToBacklog($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Init backlog select query
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getInitSelect(): \Magento\Framework\DB\Select
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select();
        $select->from(
            ['product_price'  => $this->getTable(\Bss\CustomPricing\Model\ResourceModel\ProductPrice::TABLE)],
            []
        );
        $select->columns([
            'entity_id' => 'product_price.id'
        ]);

        return $select;
    }

    /**
     * Insert backlog
     *
     * @param \Magento\Framework\DB\Select $select
     */
    protected function insertToBacklog(\Magento\Framework\DB\Select $select)
    {
        try {
            $query = $select->insertFromSelect(
                $this->getMainChangeLogTbl(),
                ['entity_id'],
                true
            );

            $this->resource->getConnection()->query($query);
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: Insert entry to backlog failed. %1", $e)
            );
        }
    }

    /**
     * Get index change log table name
     *
     * @return string
     */
    private function getMainChangeLogTbl()
    {
        //@codingStandardsIgnoreLine
        return $this->getTable(self::BSS_INDEX_CHANGE_LOG_TABLE_NAME);
    }

    /**
     * Get table name
     *
     * @param string $name
     * @return string
     */
    public function getTable($name)
    {
        return $this->resource->getTableName($name);
    }
}
