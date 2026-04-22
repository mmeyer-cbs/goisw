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
 * Interface SubRoleInterface
 *
 * @package Bss\CompanyAccount\Api\Data
 */
interface SubRoleInterface
{
    /**
     * Constants for keys of data array.
     */
    const ID = 'role_id';
    const NAME = 'role_name';
    const TYPE = 'role_type';
    const MAX_ORDER_PER_DAY = 'order_per_day';
    const MAX_ORDER_AMOUNT = 'max_order_amount';
    const CUSTOMER_ID = 'customer_id';

    /**
     * Get related company account
     *
     * @return int
     */
    public function getCompanyAccount();

    /**
     * Associate to a company account
     *
     * @param int $id
     * @return mixed
     */
    public function setCompanyAccount($id);

    /**
     * Get role id
     *
     * @return int
     */
    public function getRoleId();

    /**
     * Set role id
     *
     * @param int|null $id
     * @return void
     */
    public function setRoleId($id);

    /**
     * Get role name
     *
     * @return string
     */
    public function getRoleName();

    /**
     * Set role name
     *
     * @param string $name
     * @return void
     */
    public function setRoleName($name);

    /**
     * Get permissions string
     *
     * @return string
     */
    public function getRoleType();

    /**
     * Set role permissions
     *
     * @param string $typeStr
     * @return void
     */
    public function setRoleType($typeStr);

    /**
     * Get max order perday limit
     *
     * @return int
     */
    public function getMaxOrderPerDay();

    /**
     * Set number order per day
     *
     * @param int|null $number
     * @return void
     */
    public function setMaxOrderPerDay($number);

    /**
     * Get max order amount
     *
     * @return float
     */
    public function getMaxOrderAmount();

    /**
     * Set max order amount
     *
     * @param float|null $number
     * @return void
     */
    public function setMaxOrderAmount($number);
}
