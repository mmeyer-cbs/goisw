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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Api;

use Bss\SalesRep\Api\Data\SalesRepOrderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface SalesRepOrderRepositoryInterface
{

    /**
     * Get history by history ID.
     *
     * @param int $repId
     * @return $this
     */
    public function getById($repId);

    /**
     * Get list Sales Rep Order
     *
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete a order
     *
     * @param SalesRepOrderInterface $order
     * @return mixed
     */
    public function delete(SalesRepOrderInterface $order);

    /**
     * Delete order by id
     *
     * @param int $id
     * @return mixed
     */
    public function deleteById($id);
}
