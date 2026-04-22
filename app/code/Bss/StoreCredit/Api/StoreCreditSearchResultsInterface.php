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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface StoreCreditSearchResultsInterface
 */
interface StoreCreditSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\StoreCredit\Api\Data\StoreCreditInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\StoreCredit\Api\Data\StoreCreditInterface[] $items
     * @return \Bss\StoreCredit\Api\StoreCreditSearchResultsInterface
     */
    public function setItems(array $items);
}
