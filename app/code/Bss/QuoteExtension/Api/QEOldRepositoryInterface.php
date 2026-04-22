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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface QEOldRepositoryInterface
{
    /**
     * Get list quote magento old
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bss\QuoteExtension\Api\QEOldSearchResultsInterface|SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Save quote old of request for quote
     *
     * @param \Bss\QuoteExtension\Api\Data\QEOldInterface $qEOld
     * @return \Bss\QuoteExtension\Api\Data\QEOldInterface
     */
    public function save($qEOld);

    /**
     * Delete request for quote old
     *
     * @param \Bss\QuoteExtension\Api\Data\QEOldInterface $qEOld
     * @return mixed
     */
    public function delete($qEOld);

}
