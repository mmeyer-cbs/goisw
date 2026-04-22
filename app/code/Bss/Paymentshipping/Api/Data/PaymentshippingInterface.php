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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Api\Data;


/**
 * Interface PaymentshippingInterface
 *
 * @package Bss\Paymentshipping\Api\Data
 */
interface PaymentshippingInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';

    const TYPE = 'type';

    const WEBSITE_ID = 'website_id';

    const METHOD = 'method';

    const GROUP_IDS = "group_ids";

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     * @since 100.1.0
     */
    public function setType(string $type);

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     * @since 100.1.0
     */
    public function setWebsiteId(int $websiteId);

    /**
     * Set method
     *
     * @param string $method
     * @return $this
     * @since 100.1.0
     */
    public function setMethod(string $method);

    /**
     * Set Group IDS
     *
     * @param string $group_ids
     * @return $this
     * @since 100.1.0
     */
    public function setGroupIds(string $groupIds);

    /**
     * Retrieve entity id
     *
     * @return mixed
     */
    public function getEntityId();

    /**
     * Get type
     *
     * @return string
     * @since 100.1.0
     */
    public function getType();

    /**
     * Get webiste id
     *
     * @return int
     * @since 100.1.0
     */
    public function getWebsiteId();

    /**
     * Get method
     *
     * @return string
     * @since 100.1.0
     */
    public function getMethod();

    /**
     * Get group ids
     *
     * @return string
     * @since 100.1.0
     */
    public function getGroupIds();

}
