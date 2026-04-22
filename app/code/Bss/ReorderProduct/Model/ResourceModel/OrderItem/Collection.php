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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Model\ResourceModel\OrderItem;

/**
 * Class Collection
 *
 * @package Bss\ReorderProduct\Model\ResourceModel\OrderItem
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
{
    /**
     * Filter Order by ids
     *
     * @param array $orderids
     * @return $this
     */
    public function filterOrderIds($orderids)
    {
        $this->addFieldToFilter('order_id', ['in' => $orderids]);
        $this->addFieldToFilter('parent_item_id', ['null' => true]);
        $this->getSelect()->join(
            ['rio' => $this->getTable("bss_reorder_item_options")],
            'main_table.item_id = rio.item_id'
        );
        // @codingStandardsIgnoreStart
        $this->getSelect()
            ->columns('MAX(main_table.item_id) as reoder_item_id')
            ->columns('SUM(qty_ordered) as reoder_qty_ordered')
            ->columns('MAX(main_table.created_at) as last_purchased_date')
            ->group(['item_options', 'sku']);
        return $this;
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get child product by parent product id
     *
     * @param string $parentItemId
     * @return $this
     */
    public function getChildProduct($parentItemId)
    {
        $this->addFieldToFilter('parent_item_id', $parentItemId);
        return $this;
    }
}
