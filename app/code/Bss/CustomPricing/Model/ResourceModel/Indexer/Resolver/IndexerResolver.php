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

namespace Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver;

/**
 * Class Indexer resolver
 */
class IndexerResolver
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * IndexerResolver constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * Is rule id with customer is indexed
     *
     * @param int $ruleId
     * @param array $customerData
     * @return false|string
     */
    public function isIndexed($ruleId, $customerData)
    {
        try {
            $connection = $this->resource->getConnection();
            $select = $connection->select()->from($this->getMainIdxTableName(), "customer_group_id");
            $select->where("rule_id IN(?)", $ruleId);
            $select->where(
                sprintf(
                    "customer_group_id = %s AND website_id = %s",
                    $customerData["group_id"],
                    $customerData["website_id"]
                )
            );
            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * Get main index table name
     *
     * @return string
     */
    public function getMainIdxTableName()
    {
        //@codingStandardsIgnoreLine
        return $this->resource->getTableName(\Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice::BSS_INDEX_TABLE_NAME);
    }

    /**
     * Get resource object
     *
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResource()
    {
        return $this->resource;
    }
}
