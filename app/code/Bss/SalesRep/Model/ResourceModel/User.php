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

/**
 * Class User
 *
 * @package Bss\SalesRep\Model\ResourceModel
 */
class User extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('admin_user', 'user_id');
    }

    /**
     * Join Table User
     *
     * @return array
     */
    public function joinTableUser()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->reset()
            ->from(
                ['auth_role' => $this->getTable('authorization_role')],
                ''
            )
            ->join(
                ['admin_user' => $this->getTable('admin_user')],
                'admin_user.user_id = auth_role.user_id',
                ['user_name' => 'admin_user.username',
                    'user_id' => 'auth_role.user_id'
                ]
            );
        return $connection->fetchAssoc($select);
    }
}
