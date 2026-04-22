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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Api;

use Bss\CustomPricing\Api\Data\AppliedCustomersInterface as AppliedCustomers;
use Bss\CustomPricing\Api\Data\AppliedCustomersSearchResultsInterface as AppliedCustomersSearchResults;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface AppliedCustomersRepositoryInterface
 */
interface AppliedCustomersRepositoryInterface extends RelatedRepositoryInterface
{
    /**
     * Save applied customer data
     *
     * @param AppliedCustomers $appliedCustomers
     *
     * @return AppliedCustomers
     * @throws CouldNotSaveException
     */
    public function save(AppliedCustomers $appliedCustomers);

    /**
     * Get applied customer by id
     *
     * @param int $id
     *
     * @return AppliedCustomers
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve applied customers match the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return AppliedCustomersSearchResults
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete applied customer
     *
     * @param AppliedCustomers $appliedCustomers
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(AppliedCustomers $appliedCustomers);

    /**
     * Delete applied customer by id
     *
     * @param int $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Get applied customer by rule and customer id
     *
     * @param int $ruleId
     * @param int $customerId
     * @return \Bss\CustomPricing\Model\AppliedCustomers|false
     */
    public function getBy($ruleId, $customerId);

    /**
     * Check if customer has applied in rule
     *
     * @param int $ruleId
     * @param int $customerId
     * @return bool|string
     */
    public function hasCustomer($ruleId, $customerId);
}
