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
interface QuoteVersionRepositoryInterface
{
    /**
     * Get list quote version:comment
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bss\QuoteExtension\Api\QuoteVersionSearchResultsInterface|SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Save quote version: comment
     *
     * @param \Bss\QuoteExtension\Api\Data\QuoteVersionInterface $quoteVersion
     * @return \Bss\QuoteExtension\Api\Data\QuoteVersionInterface
     */
    public function save($quoteVersion);

    /**
     * Get quote version by id
     *
     * @param int $id
     * @return \Bss\QuoteExtension\Api\Data\QuoteVersionInterface
     */
    public function getById($id);

    /**
     * Get manage quote by customer Id
     *
     * @param int $quoteId
     * @return \Bss\QuoteExtension\Api\QuoteVersionSearchResultsInterface|SearchResultsInterface
     */
    public function getByQuoteId($quoteId);

    /**
     * Delete quote version by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);

    /**
     * Delete quote version comment
     *
     * @param \Bss\QuoteExtension\Api\Data\QuoteVersionInterface $quoteVersion
     * @return bool
     */
    public function delete($quoteVersion);

}
