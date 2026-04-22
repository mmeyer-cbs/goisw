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
 * Interface PriceRuleSearchResultsInterface
 */
interface PriceRuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get rule items
     *
     * @return \Bss\CustomPricing\Api\Data\PriceRuleInterface[]
     */
    public function getItems();

    /**
     * Set rule items
     *
     * @param \Bss\CustomPricing\Api\Data\PriceRuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
