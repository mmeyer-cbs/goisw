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

namespace Bss\CustomPricing\Model\ResourceModel;

use Magento\Bundle\Model\Product\Price as BundlePriceType;
/**
 * Class Product
 */
class Product
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get default connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        if ($this->connection == null) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }

    /**
     * Check if product is fixed price
     *
     * @param int $productId
     * @return bool
     */
    public function isFixedPriceType($productId)
    {
        if ($type = $this->getPriceType($productId)) {
            return $type["value"] == BundlePriceType::PRICE_TYPE_FIXED;
        }
        return !$type;
    }

    /**
     * Get price type of product by id
     *
     * @param int $productId
     * @return int
     */
    private function getPriceType($productId)
    {
        try {
            $connection = $this->getConnection();
            $subSelect = $connection->select()->from(
                'eav_attribute',
                'attribute_id'
            )->where('attribute_code = ?', 'price_type');

            $select = $connection->select()->from(
                'catalog_product_entity_int',
                'value'
            )->where('entity_id = ?', $productId)
            ->where('attribute_id = (?)', $subSelect);

            return $connection->fetchRow($select);
        } catch (\Exception $e) {
            return false;
        }
    }
}
