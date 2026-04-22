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

use Bss\SalesRep\Api\Data\SalesRepInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface SalesRepRepositoryInterface
{
    /**
     * Get Sales Rep by ID.
     *
     * @param int $repId
     * @return $this
     */
    public function getById($repId);

    /**
     * Get history by history ID.
     *
     * @param int $userId
     * @return $this
     */
    public function getByUserId($userId);

    /**
     * Get list Sales Rep Order
     *
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete Sales Rep
     *
     * @param SalesRepInterface $salesRep
     * @return mixed
     */
    public function delete(SalesRepInterface $salesRep);

    /**
     * Delete order by id
     *
     * @param int $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param int $role
     * @return mixed
     */
    public function getListByRoleId($roleId);

}
