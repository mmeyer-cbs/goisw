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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Statement_Interface;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Model\ResourceModel
 */
class SalesRepOrder extends AbstractDb
{
    /**
     * Define table bss_sales_rep
     */
    protected function _construct()
    {
        $this->_init('bss_sales_rep_order', 'id');
    }

    /**
     * Join Table Order
     *
     * @return Zend_Db_Statement_Interface
     */
    public function joinTableOrder()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->reset()
            ->from(
                ['admin_user' => $this->getTable('admin_user')],
                ''
            )
            ->join(
                ['bss_sales_rep_order' => $this->getTable('bss_sales_rep_order')],
                'admin_user.user_id = bss_sales_rep_order.user_id',
                ['user_name' => 'admin_user.username',
                    'user_id' => 'bss_sales_rep_order.user_id',
                    'order_id' => 'bss_sales_rep_order.order_id'
                ]
            );
        return $connection->query($select);
    }
}
