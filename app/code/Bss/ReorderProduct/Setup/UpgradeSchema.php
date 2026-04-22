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
namespace Bss\ReorderProduct\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package Bss\ReorderProduct\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Drop column reorder_item_options from sales_order_item table
     *
     * Create new bss_reorder_item_options table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), 'reorder_item_options');
            /**
             * Create table 'reorder_item_options'
             */
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('bss_reorder_item_options')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'ID'
                )
                ->addColumn(
                    'item_options',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Item Options'
                )
                ->addIndex(
                    $setup->getIdxName('bss_reorder_item_options', ['item_id']),
                    ['item_id']
                )
                ->setComment('Reorder Item Options');
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
