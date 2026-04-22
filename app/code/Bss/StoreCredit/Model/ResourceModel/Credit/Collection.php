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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Model\ResourceModel\Credit;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Bss\StoreCredit\Model\Credit;
use Bss\StoreCredit\Model\ResourceModel\Credit as ResourceModelCredit;

/**
 * Class Collection
 * @package Bss\StoreCredit\Model\ResourceModel\Credit
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(Credit::class, ResourceModelCredit::class);
    }
}
