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
 * Interface SubUserOrderSearchResultsInterface
 *
 * @package Bss\CompanyAccount\Api\Data
 */
interface SubUserOrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface[] $items
     * @return $this
     */
    public function setItems($items);
}
