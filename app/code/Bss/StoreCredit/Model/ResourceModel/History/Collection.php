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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Model\ResourceModel\History;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Bss\StoreCredit\Model\History;
use Bss\StoreCredit\Model\ResourceModel\History as ResourceModelHistory;

/**
 * Class Collection
 * @package Bss\StoreCredit\Model\ResourceModel\History
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(History::class, ResourceModelHistory::class);
    }

    /**
     * Prepare for store credit history
     *
     * @param int $websiteId
     * @return $this
     */
    public function prepareForStoreCreditTransactions($websiteId)
    {
        $this->addFieldToFilter(
            'main_table.website_id',
            ['eq' => $websiteId]
        );

        return $this;
    }

    /**
     * Get report data from history
     *
     * @param array $interval
     * @param string $period
     * @param bool|null $refund
     * @param int|null $websiteId
     * @return float
     */
    public function getReport($interval, $period, $refund = null, $websiteId = null)
    {
        $connection = $this->getConnection();
        $selectedColumns = [
            'period' => sprintf('MAX(%s)', $connection->getDateFormatSql('main_table.updated_time', '%Y-%m-%d')),
            'change_amount_totals' => 'SUM(main_table.change_amount)'
        ];
        if ('year' == $period) {
            $selectedColumns['period'] = $connection->getDateFormatSql('main_table.updated_time', '%Y');
        } elseif ('month' == $period) {
            $selectedColumns['period'] = $connection->getDateFormatSql('main_table.updated_time', '%Y-%m');
        }
        if ($refund) {
            $where = 'main_table.change_amount >= 0';
        } else {
            $where = 'main_table.change_amount < 0';
        }

        $select = $this->getSelect()->columns(
            $selectedColumns
        )->where(
            'main_table.updated_time >= ?',
            $interval['start']
        )->where(
            'main_table.updated_time <= ?',
            $interval['end']
        )->where($where);

       $this->filterByWebsiteId($select, $websiteId);
        $data = $connection->fetchRow($select);
        if ($data['change_amount_totals']) {
            return abs($data['change_amount_totals']);
        }
        return 0;
    }

    /**
     * Filter By website id
     *
     * @param $select
     * @param int|null $websiteId
     */
    public function filterByWebsiteId($select, $websiteId)
    {
        if ($websiteId) {
            $select->where("main_table.website_id = ?", $websiteId);
        }
    }

    /**
     * @param int $orderId
     * @return \Bss\StoreCredit\Model\History | \Magento\Framework\DataObject
     */
    public function getStoreCreditHistoryRecord($orderId)
    {
        $this->addFieldToFilter(
            'main_table.order_id',
            ['eq' => $orderId]
        );

        return $this->getLastItem();
    }
}
