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

use Bss\CustomPricing\Api\Data\PriceRuleInterface as PriceRule;
use Bss\CustomPricing\Model\ResourceModel\PriceRule as PriceRuleResource;
use Bss\CustomPricing\Api\Data\PriceRuleSearchResultsInterface as PriceRuleSearchResults;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface PriceRuleRepositoryInterface
 */
interface PriceRuleRepositoryInterface
{
    /**
     * Save Price Rule data
     *
     * @param PriceRule $priceRule
     *
     * @return PriceRule
     * @throws CouldNotSaveException
     */
    public function save(PriceRule $priceRule);

    /**
     * Get Price rule by id
     *
     * @param int $id
     * @param array $with
     *
     * @return PriceRule
     * @throws NoSuchEntityException
     */
    public function getById($id, $with = []);

    /**
     * Retrieve Price Rules match the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @param array $with
     *
     * @return PriceRuleSearchResults
     */
    public function getList(SearchCriteriaInterface $criteria, $with = []);

    /**
     * Delete price rule
     *
     * @param PriceRule $priceRule
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PriceRule $priceRule);

    /**
     * Delete price rule by id
     *
     * @param int $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
