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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Api;

use Bss\CompanyAccount\Api\Data\SubUserOrderInterface as SubUserOrder;
use Bss\CompanyAccount\Api\Data\SubUserOrderSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface SubUserOrderRepositoryInterface
 *
 * @package Bss\CompanyAccount\Api
 */
interface SubUserOrderRepositoryInterface
{
    /**
     * Save data
     *
     * @param SubUserOrder $userOrder
     *
     * @return SubUserOrder
     * @throws CouldNotSaveException
     */
    public function save(SubUserOrder $userOrder);

    /**
     * Get data by id
     *
     * @param int $id
     *
     * @return SubUserOrder
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get UserOrder by order id
     *
     * @param int $orderId
     * @return SubUserOrder|bool
     */
    public function getByOrderId($orderId);

    /**
     * Get order ids by subuser
     *
     * @param int $subUserId
     * @return array
     */
    public function getBySubUser($subUserId);

    /**
     * Retrieve sub-user order matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return SubUserOrderSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Destroy sub user order
     *
     * @param SubUserOrder $userOrder
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SubUserOrder $userOrder);

    /**
     * Destroy sub user order by id
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
