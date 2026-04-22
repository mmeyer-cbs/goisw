<?php
/**ore
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

/**
 * StoreCredit Credit Management
 *
 * @api
 * @since 100.0.0
 */
interface StoreCreditManagementInterface
{
    /**
     * Get module configs
     *
     * @param int $storeId
     * @return mixed|string[]
     */
    public function getConfig($storeId);

    /**
     * Get Credit by customer id, website id
     *
     * @param int $customerId
     * @param int $websiteId
     * @return mixed|array|null
     */
    public function getCredit($customerId, $websiteId);

    /**
     * Get All Credit by customer id
     *
     * @param int $customerId
     * @return \Bss\StoreCredit\Api\Data\StoreCreditInterface[]
     */
    public function getAllCreditCustomerId($customerId);

    /**
     * Get all history credit by customer ID
     *
     * @param int $customerId
     * @return \Bss\StoreCredit\Api\Data\HistoryInterface[]|\Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getAllHistoryCreditCustomerId($customerId);

    /**
     * Get history by id
     *
     * @param int $historyId
     * @return mixed|array|null
     */
    public function getHistoryById($historyId);

    /**
     * Get history interval
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Bss\StoreCredit\Api\Data\HistoryInterface[]|\Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getHistoryInInterval($startDate, $endDate);

    /**
     * Get history interval
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $period
     * @return \Bss\StoreCredit\Api\Data\HistoryInterface[]|\Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getReport($startDate, $endDate, $period);

}
