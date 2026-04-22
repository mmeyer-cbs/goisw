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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Api;

use Bss\CompanyCredit\Api\Data\HistoryInterface;

/**
 * @api
 */
interface HistoryRepositoryInterface
{
    /**
     * Get history by  ID.
     *
     * @param int $Id
     * @return \Bss\CompanyCredit\Api\HistoryRepositoryInterface
     */
    public function getById(int $Id);

    /**
     * Get list history
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);

    /**
     * Save credit
     *
     * @param HistoryInterface $historyInterface
     * @return mixed
     */
    public function save(HistoryInterface $historyInterface);
}
