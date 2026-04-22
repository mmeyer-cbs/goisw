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
namespace Bss\CompanyAccount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SubUser
 *
 * @package Bss\CompanyAccount\Model\ResourceModel
 */
class SubUserOrder extends AbstractDb
{
    const TABLE = 'bss_sub_user_order';
    const ID = 'entity_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, self::ID);
    }

    /**
     * Load data by order id
     *
     * @param int $orderId
     * @param \Bss\CompanyAccount\Model\SubUserOrder $subUserOrder
     * @return SubUserOrder
     */
    public function loadByOrderId($orderId, $subUserOrder)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('order_id', $orderId, $subUserOrder);
        $data = $connection->fetchRow($select);

        if ($data) {
            $subUserOrder->setData($data);
        }

        $this->_afterLoad($subUserOrder);

        return $this;
    }
}
