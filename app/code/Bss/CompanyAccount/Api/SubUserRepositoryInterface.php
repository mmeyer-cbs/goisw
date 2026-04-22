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

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface SubUserRepositoryInterface
 *
 * @package Bss\CompanyAccount\Api
 */
interface SubUserRepositoryInterface extends SubModelRepositoryInterface
{
    /**
     * Save user information
     *
     * @param SubUserInterface $user
     *
     * @return SubUserInterface
     * @throws AlreadyExistsException
     */
    public function save(SubUserInterface $user);

    /**
     * Create user information
     *
     * @param SubUserInterface $user
     *
     * @return SubUserInterface
     * @throws AlreadyExistsException
     */
    public function create(SubUserInterface $user);

    /**
     * Get sub user by id
     *
     * @param int $id
     * @return SubUserInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve sub users matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @param mixed $with - relation fields
     * @return SubUserSearchResultsInterface
     * @throws \Bss\CompanyAccount\Exception\RelationMethodNotFoundException
     */
    public function getList(SearchCriteriaInterface $criteria, $with = null);

    /**
     * Destroy sub user
     *
     * @param SubUserInterface $user
     * @return bool|UserResource
     * @throws CouldNotDeleteException
     */
    public function delete(SubuserInterface $user);

    /**
     * Destroy sub user by id
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Get quote by sub-user
     *
     * @param int|SubUserInterface $subUser
     * @return null|\Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteBySubUser($subUser);

    /**
     * Get sub-user list by role
     *
     * @param int $roleId
     *
     * @return bool|\Bss\CompanyAccount\Model\ResourceModel\SubUser\Collection
     * @deprecated use getList with search SearchCriteria instead
     */
    public function getByRole($roleId);

    /**
     * Validate unique email for sub-user
     *
     * @param int $customerId
     * @param string $email
     * @param int|null $subId
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function validateUniqueSubMail($customerId, $email, $subId = null);
}
