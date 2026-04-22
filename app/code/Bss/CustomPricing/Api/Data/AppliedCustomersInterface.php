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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Api\Data;

/**
 * Interface AppliedCustomersInterface
 */
interface AppliedCustomersInterface
{
    const ID = "id";
    const CUSTOMER_FIRST_NAME = "customer_first_name";
    const CUSTOMER_LAST_NAME = "customer_last_name";
    const RULE_ID = "rule_id";
    const CUSTOMER_ID = "customer_id";
    const APPLIED_RULE = "applied_rule";

    /**
     * Get customer first name
     *
     * @return string
     */
    public function getCustomerFirstName();

    /**
     * Set customer first name
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCustomerFirstName($val);

    /**
     * Get customer last name
     *
     * @return string
     */
    public function getCustomerLastName();

    /**
     * Set customer last name
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCustomerLastName($val);

    /**
     * Get rule id
     *
     * @return string
     */
    public function getRuleId();

    /**
     * Set rule id
     *
     * @param string $val
     *
     * @return $this
     */
    public function setRuleId($val);

    /**
     * Get customer id
     *
     * @return string
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCustomerId($val);

    /**
     * Get applied rule customer status
     *
     * @return string
     */
    public function getAppliedRule();

    /**
     * Set applied rule customer status
     *
     * @param string $val
     *
     * @return $this
     */
    public function setAppliedRule($val);
}
