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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface QuoteItemRepositoryInterface
{
    /**
     * Get list manage quote
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bss\QuoteExtension\Api\QuoteItemSearchResultsInterface|SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Save manage quote
     *
     * @param \Bss\QuoteExtension\Api\Data\QuoteItemInterface $manageQuote
     * @return \Bss\QuoteExtension\Api\Data\QuoteItemInterface
     */
    public function save($manageQuote);

    /**
     * Get manage quote by id
     *
     * @param int $entityId
     * @return \Bss\QuoteExtension\Api\Data\QuoteItemInterface
     */
    public function getById($entityId);

    /**
     * Get manage quote by customer Id
     *
     * @param int $entityId
     * @return \Bss\QuoteExtension\Api\QuoteItemSearchResultsInterface|SearchResultsInterface
     */
    public function getByItemId($customerId);

    /**
     * Delete quote item comment by id
     *
     * @param int $entityId
     * @return bool
     */
    public function deleteById($entityId);

    /**
     * Delete quote item comment
     *
     * @param \Bss\QuoteExtension\Api\Data\QuoteItemInterface $manageQuote
     * @return bool
     */
    public function delete($manageQuote);

}
