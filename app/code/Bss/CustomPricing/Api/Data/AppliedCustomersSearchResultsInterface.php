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
 * Interface AppliedCustomersSearchResultsInterface
 */
interface AppliedCustomersSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get applied customer items
     *
     * @return \Bss\CustomPricing\Api\Data\AppliedCustomersInterface[]
     */
    public function getItems();

    /**
     * Set applied customer items
     *
     * @param \Bss\CustomPricing\Api\Data\AppliedCustomersInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
