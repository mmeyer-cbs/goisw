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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\CustomerAttributes\Api\Data;

interface AddressAttributeInterface
{
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get AttrId
     *
     * @return int
     */
    public function getAttrId();

    /**
     * Set AttrId
     *
     * @param int $attrId
     * @return $this
     */
    public function setAttrId($attrId);

    /**
     * Get DependentsData
     *
     * @return string
     */
    public function getDependentsData();

    /**
     * Set DependentsData
     *
     * @param string $dependentsData
     * @return $this
     */
    public function setDependentsData($dependentsData);
}
