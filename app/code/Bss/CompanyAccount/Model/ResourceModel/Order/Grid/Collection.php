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
namespace Bss\CompanyAccount\Model\ResourceModel\Order\Grid;

/**
 * Class Collection
 *
 * @package Bss\CompanyAccount\Model\ResourceModel\Order\Grid
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /**
     * Add data to column Created By
     *
     * @return Collection|\Magento\Sales\Model\ResourceModel\Order\Grid\Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['sub_order' => $this->getTable('bss_sub_user_order')],
            'main_table.entity_id = sub_order.order_id',
            'sub_id'
        )->joinLeft(
            ['sub_user' => $this->getTable('bss_sub_user')],
            'sub_order.sub_id = sub_user.sub_id',
            ['ca_sub_user_name' => 'sub_user.sub_name']
        );
    }
}
