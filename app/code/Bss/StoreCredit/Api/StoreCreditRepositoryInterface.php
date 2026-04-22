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

/**
 * @api
 */
interface StoreCreditRepositoryInterface
{
    /**
     * Retrieve customer.
     *
     * @param int|null $customerId
     * @param int|null $websiteId
     * @return \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    public function get($customerId = null, $websiteId = null);

    /**
     * Get list store credit
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Bss\StoreCredit\Api\StoreCreditSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Apply store credit
     *
     * @param float $amount
     * @return \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    public function apply($amount);
}
