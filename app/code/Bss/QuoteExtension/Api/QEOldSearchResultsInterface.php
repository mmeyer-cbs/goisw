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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface QEOldSearchResultsInterface
 */
interface QEOldSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\QuoteExtension\Api\Data\QEOldInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\QuoteExtension\Api\Data\QEOldInterface[] $items
     * @return \Bss\QuoteExtension\Api\QEOldSearchResultsInterface
     */
    public function setItems(array $items);
}
