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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ManageQuoteSearchResultsInterface
 */
interface ManageQuoteSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\QuoteExtension\Api\Data\ManageQuoteInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\QuoteExtension\Api\Data\ManageQuoteInterface[] $items
     * @return \Bss\QuoteExtension\Api\ManageQuoteSearchResultsInterface
     */
    public function setItems(array $items);
}
