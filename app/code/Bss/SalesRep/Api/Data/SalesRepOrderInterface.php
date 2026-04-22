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
namespace Bss\SalesRep\Api\Data;

/**
 * Interface SalesRepOrderInterface
 *
 * @package Bss\SalesRep\Api\Data
 */
interface SalesRepOrderInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const USER_ID = 'user_id';

    const ORDER_ID = 'order_id';

    /**#@-*/

    /**
     * Get Sales Rep order
     *
     * @return mixed
     */
    public function getSalesRepOrder();

    /**
     * Set User Id
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get user id
     *
     * @return int
     */
    public function getUserId();

    /**
     * Get Order id
     *
     * @return string
     */
    public function getOrderId();
}
