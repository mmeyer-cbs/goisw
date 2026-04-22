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

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface as SubUserQuote;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface SubUserQuoteRepositoryInterface
 *
 * @package Bss\CompanyAccount\Api
 */
interface SubUserQuoteRepositoryInterface
{
    /**
     * Save data
     *
     * @param SubUserQuote $userQuote
     *
     * @return SubUserQuote
     * @throws CouldNotSaveException
     */
    public function save(SubUserQuote $userQuote);

    /**
     * Get data by id
     *
     * @param int $id
     *
     * @return SubUserQuote
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get UserQuote by quote id
     *
     * @param int $quoteId
     * @return SubUserQuote|bool
     */
    public function getByQuoteId($quoteId);

    /**
     * Get quote ids by subuser
     *
     * @param int $subUserId
     * @return array
     */
    public function getBySubUser($subUserId);

    /**
     * Retrieve sub-user quote matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Destroy sub user quote
     *
     * @param SubUserQuote $userQuote
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($userQuote);

    /**
     * Destroy sub user quote by id
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Get UserQuote by user id
     *
     * @param int $id
     * @param string $type
     * @return SubUserQuote|bool
     */
    public function getByUserId($id, $type);
}
