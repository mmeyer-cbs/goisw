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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model\ResourceModel\Remind;

use Bss\CompanyCredit\Model\Remind;
use Bss\CompanyCredit\Model\ResourceModel\Remind as ResourceRemind;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Construct.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Remind::class, ResourceRemind::class);
    }

    /**
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
            ['history' => 'bss_companycredit_credit_history'],
            "main_table.id_history = history.id",
            [
                'order_id' => 'history.order_id',
                'po_number' => 'history.po_number',
                'customer_id' => 'history.customer_id',
                'payment_due_date' => 'history.payment_due_date'
            ]
        );
    }
}
