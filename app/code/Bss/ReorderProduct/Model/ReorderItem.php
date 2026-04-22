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
namespace Bss\ReorderProduct\Model;

/**
 * Class ReorderItem
 *
 * @package Bss\ReorderProduct\Model
 */
class ReorderItem extends \Magento\Sales\Model\Order\Item
{
    /**
     * { @inheritdoc }
     */
    protected function _construct()
    {
        $this->_init(\Bss\ReorderProduct\Model\ResourceModel\ReorderItem::class);
    }
}
