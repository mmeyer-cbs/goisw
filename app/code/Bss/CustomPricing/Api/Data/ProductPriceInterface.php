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
 * Interface ProductPriceInterface
 */
interface ProductPriceInterface
{
    const ID = "id";
    const NAME = "name";
    const ORIGIN_PRICE = "origin_price";
    const CUSTOM_PRICE = "custom_price";
    const RULE_ID = "rule_id";
    const PRODUCT_ID = "product_id";
    const PRODUCT_SKU = "product_sku";
    const TYPE_ID = "type_id";
    const PRICE_METHOD = "price_type";
    const PRICE_VALUE = "price_value";

    /**
     * Get product price rule name
     *
     * @return string
     */
    public function getName();

    /**
     * Set product price rule name
     *
     * @param string $val
     *
     * @return $this
     */
    public function setName($val);

    /**
     * Get origin product price rule
     *
     * @return int
     */
    public function getOriginPrice();

    /**
     * Set origin product price rule
     *
     * @param int $val
     *
     * @return $this
     */
    public function setOriginPrice($val);

    /**
     * Get custom product price rule
     *
     * @return string
     */
    public function getCustomPrice();

    /**
     * Set custom product price rule
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCustomPrice($val);

    /**
     * Get rule id
     *
     * @return int
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
     * Get related product id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set product id for custom price
     *
     * @param int $val
     *
     * @return $this
     */
    public function setProductId($val);

    /**
     * Get related product sku
     *
     * @return int
     */
    public function getProductSku();

    /**
     * Set product sku for custom price
     *
     * @param int $val
     *
     * @return $this
     */
    public function setProductSku($val);

    /**
     * Get product type
     *
     * @return int
     */
    public function getTypeId();

    /**
     * Set product type id for custom price
     *
     * @param string $val
     *
     * @return $this
     */
    public function setTypeId($val);

    /**
     * Get price method
     *
     * @return string
     * @since 1.0.7
     * @see PriceTypeOption
     */
    public function getPriceMethod();

    /**
     * Set price method for custom price
     *
     * @param int $priceMethod
     * @return $this
     * @since 1.0.7
     */
    public function setPriceMethod(int $priceMethod);

    /**
     * Get price value
     *
     * @return float
     * @since 1.0.7
     */
    public function getPriceValue();

    /**
     * Set price value
     *
     * @param float|null $priceValue
     * @return $this
     * @since 1.0.7
     */
    public function setPriceValue(?float $priceValue);
}
