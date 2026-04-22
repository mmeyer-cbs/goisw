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
namespace Bss\ReorderProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SaveReorderItemOptions
 *
 * @package Bss\ReorderProduct\Model\ResourceModel
 */
class SaveReorderItemOptions extends AbstractDb
{
    /**
     * { @inheritdoc }
     */
    public function _construct()
    {
        $this->_init('bss_reorder_item_options', 'item_id');
    }

    /**
     * Save item option
     *
     * @param int $itemId
     * @param string $itemOptions
     */
    public function saveItemsOption($itemId, $itemOptions)
    {
        $connection = $this->getConnection();
        $bind = [
            'item_id' => $itemId,
            'item_options' => $itemOptions
        ];
        $connection->insert($this->getTable('bss_reorder_item_options'), $bind);
    }

    /**
     * Delete item in bss reorder table by item id
     *
     * @param int $itemId
     */
    public function deleteLocations($itemId)
    {
        $this->getConnection()->delete($this->getTable('bss_reorder_item_options'), ['item_id=?' => $itemId]);
    }

    /**
     * Clear all data
     */
    public function deleteAllRow()
    {
        $this->getConnection()->delete($this->getTable('bss_reorder_item_options'));
    }
}
