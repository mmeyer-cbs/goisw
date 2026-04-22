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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Api\Data;

/**
 * Interface SubUserOrderInterface
 *
 * @package Bss\CompanyAccount\Api\Data
 */
interface SubUserOrderInterface
{
    /**
     * Constants for keys of data array.
     */
    const ID = 'entity_id';
    const SUB_USER_ID = 'sub_id';
    const ORDER_ID = 'order_id';
    const GRAND_TOTAL = 'grand_total';
    const SUB_USER_INFO = 'sub_user_info';

    // Relation
    const SUB_USER = "sub_user";
    const ORDER = "order";

    /**
     * Get sub-user info
     *
     * @return array
     */
    public function getSubUserInfo();

    /**
     * Set sub-user info
     *
     * @param string $data
     * @return void
     */
    public function setSubUserInfo($data);

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * Get sub-user id
     *
     * @return int
     */
    public function getSubId();

    /**
     * Set sub-user id
     *
     * @param int $id
     * @return void
     */
    public function setSubId($id);

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @param int $id
     * @return void
     */
    public function setOrderId($id);

    /**
     * Get grand total
     *
     * @return string
     */
    public function getGrandTotal();

    /**
     * Set grand total
     *
     * @param string $total
     * @return void
     */
    public function setGrandTotal($total);

    /**
     * Get related sub-user
     *
     * @return SubUserInterface|null
     */
    public function subUser(): ?SubUserInterface;
}
