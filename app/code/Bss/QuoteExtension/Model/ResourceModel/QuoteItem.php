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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\ResourceModel;

/**
 * Class QuoteItem
 *
 * @package Bss\QuoteExtension\Model\ResourceModel
 */
class QuoteItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_extension_item', 'id');
    }

    /**
     * @param int $itemId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findItemComment($itemId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'item_id = ?',
            $itemId
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertMultiple($data)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $connection->insertMultiple($table, $data);
    }

    /**
     * @param array $bind
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update($bind)
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            $bind
        );
    }
}
