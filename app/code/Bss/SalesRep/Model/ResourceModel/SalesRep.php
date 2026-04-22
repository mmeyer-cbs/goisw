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
use Zend_Db_Statement_Exception;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Model\ResourceModel
 */
class SalesRep extends AbstractDb
{
    /**
     * Define table bss_sales_rep
     */
    protected function _construct()
    {
        $this->_init('bss_sales_rep', 'rep_id');
    }

    /**
     * Join bss_sales_rep with admin_user
     *
     * @param int $id
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function joinTableUser($id)
    {
        $data = [];
        $connection = $this->getConnection();
        $select = $connection->select()->reset()
            ->from(
                ['admin_user' => $this->getTable('admin_user')],
                ''
            )
            ->join(
                ['bss_sales_rep' => $this->getTable('bss_sales_rep')],
                'admin_user.user_id = bss_sales_rep.user_id',
                [   'user_name' => 'admin_user.username',
                    'user_id' => 'bss_sales_rep.user_id',
                    'information' => 'bss_sales_rep.information',
                    'name' => "CONCAT(admin_user.firstname , ' ' , admin_user.lastname)"
                ]
            )->where('admin_user.user_id='.$id);
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }
        return $data;
    }
}
