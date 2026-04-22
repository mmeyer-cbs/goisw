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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Model\ResourceModel\History\Grid;

use Bss\StoreCredit\Model\ResourceModel\History\Collection as ResourceModelCollection;

/**
 * Class Collection
 */
class Collection extends ResourceModelCollection
{
    /**
     * Init select
     *
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            [
                'customer' => $this->getResource()->getTable('customer_grid_flat')
            ],
            'main_table.customer_id = customer.entity_id',
            [
                'email' => 'customer.email',
                'name' => 'customer.name'
            ]
        );
        $this->getSelect()->joinLeft(
            [
                'sales_order_table' => $this->getResource()->getTable('sales_order')
            ],
            'main_table.order_id = sales_order_table.entity_id',
            [
                'order_increment_id' => 'sales_order_table.increment_id'
            ]
        )->joinLeft(
            [
                'creditmemo_table' => $this->getResource()->getTable('sales_creditmemo')
            ],
            'main_table.creditmemo_id = creditmemo_table.entity_id',
            [
                'creditmemo_increment_id' => 'creditmemo_table.increment_id'
            ]
        );
    }
}
