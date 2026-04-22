<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Model\ResourceModel\SubUserOrder;

/**
 * Class Collection
 *
 * @package Bss\CompanyAccount\Model\ResourceModel\SubUserOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\CompanyAccount\Model\SubUserOrder::class,
            \Bss\CompanyAccount\Model\ResourceModel\SubUserOrder::class
        );
    }

    /**
     * Load role detail for sub-user order
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|void
     */
    public function getReportData()
    {
        $this->getSelect()->joinLeft(
            ['sub_user' => $this->getTable('bss_sub_user')],
            'main_table.sub_id = sub_user.sub_id',
            ['customer_id', 'sub_user.created_at']
        );
        $this->getSelect()->columns(['count' => new \Zend_Db_Expr('COUNT(*)')])
            ->group('main_table.sub_id');
        $this->getSelect()->columns(['grand_total' => new \Zend_Db_Expr('SUM(grand_total)')])
            ->group('main_table.sub_id');
        return $this;
    }
}
