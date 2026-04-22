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

use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\Data\SubRoleSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface SubRoleRepositoryInterface
 *
 * @package Bss\CompanyAccount\Api
 */
interface SubRoleRepositoryInterface extends SubModelRepositoryInterface
{
    /**
     * Save a role
     *
     * @param SubRoleInterface $role
     * @return SubRoleInterface
     */
    public function save(SubRoleInterface $role);

    /**
     * Get role by id
     *
     * @param int $id
     * @return SubRoleInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve roles matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return SubRoleSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Destroy a role
     *
     * @param SubRoleInterface $role
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SubRoleInterface $role);

    /**
     * Destroy role by id
     *
     * @param int $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Get list roles by customer id (include admin role)
     *
     * @param int $customerId
     * @return SubRoleSearchResultsInterface
     */
    public function getListByCustomer($customerId): SearchResultsInterface;
}
