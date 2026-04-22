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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface HistoryRepositoryInterface
{
    /**
     * Get history by history ID.
     *
     * @param int $historyId
     * @return \Bss\StoreCredit\Api\HistoryCreditSearchResultsInterface
     */
    public function getById($historyId);

    /**
     * Get list store credit
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Bss\StoreCredit\Api\HistoryCreditSearchResultsInterface|SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);
}
