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
 * Interface PriceRuleInterface
 * @method int getId() get Rule id
 */
interface PriceRuleInterface
{
    const ID = "id";
    const NAME = "name";
    const STATUS = "status";
    const DESCRIPTION = "description";
    const PRIORITY = "priority";
    const WEBSITE_ID = 'website_id';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const PRODUCT_CONDITIONS_SERIALIZED = 'product_serialized';
    const CUSTOMER_CONDITIONS_SERIALIZED = 'customer_serialized';
    const IS_NOT_LOGGED_RULE = 'is_not_logged_rule';
    const DEFAULT_PRICE_METHOD = 'default_price_type';
    const DEFAULT_PRICE_VALUE = 'default_price_value';

    /**
     * Get price rule name
     *
     * @return string
     */
    public function getName();

    /**
     * Set price rule name
     *
     * @param string $val
     *
     * @return $this
     */
    public function setName($val);

    /**
     * Get price rule status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status to Price Rule
     *
     * @param int $val
     *
     * @return $this
     */
    public function setStatus($val);

    /**
     * Get description about this Rule
     *
     * @return string
     */
    public function getDescription();

    /**
     * Describe about the Price Rule
     *
     * @param string $val
     *
     * @return $this
     */
    public function setDescription($val);

    /**
     * The priority of the Rule with others
     *
     * @return int
     */
    public function getPriority();

    /**
     * Set priority to the Rule
     *
     * @param string $val
     *
     * @return $this
     */
    public function setPriority($val);

    /**
     * Get website id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website Id
     *
     * @param int $val
     *
     * @return $this
     */
    public function setWebsiteId($val);

    /**
     * Get serialized condition string
     *
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * Set serialized conditions
     *
     * @param string $val
     *
     * @return $this
     */
    public function setConditionsSerialized($val);

    /**
     * Get serialized customer conditions
     *
     * @return string
     */
    public function getCustomerConditionsSerialized();

    /**
     * Set customer conditions for price rule
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCustomerConditionsSerialized($val);

    /**
     * Get serialized product conditions
     *
     * @return string
     */
    public function getProductConditionsSerialized();

    /**
     * Set product conditions for price rule
     *
     * @param string $val
     *
     * @return $this
     */
    public function setProductConditionsSerialized($val);

    /**
     * Get not logged in rule
     *
     * @return string
     */
    public function getIsNotLoggedRule();

    /**
     * Set not logged in rule
     *
     * @param string $val
     *
     * @return $this
     */
    public function setIsNotLoggedRule($val);

    /**
     * Get rule price method
     *
     * @return string
     * @since 1.0.7
     */
    public function getDefaultPriceMethod();

    /**
     * Set rule price method
     *
     * @param string|null $val
     *
     * @return $this
     * @since 1.0.7
     */
    public function setDefaultPriceMethod($val);

    /**
     * Get default price value
     *
     * @return float
     * @since 1.0.7
     */
    public function getDefaultPriceValue();

    /**
     * Set default price value
     *
     * @return $this
     * @since 1.0.7
     */
    public function setDefaultPriceValue(?float $value);
}
