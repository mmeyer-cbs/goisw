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

use Bss\CustomPricing\Api\Data\ProductPriceInterface as ProductPriceRule;
use Bss\CustomPricing\Api\Data\ProductPriceSearchResultsInterface as ProductPriceSearchResults;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ProductPriceRepositoryInterface
 */
interface ProductPriceRepositoryInterface extends RelatedRepositoryInterface
{
    /**
     * Save Price Rule data
     *
     * @param ProductPriceRule $priceRule
     *
     * @return ProductPriceRule
     * @throws CouldNotSaveException
     */
    public function save(ProductPriceRule $priceRule);

    /**
     * Get product price rule by id
     *
     * @param int $id
     *
     * @return ProductPriceRule
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve product price rules match the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return ProductPriceSearchResults
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete product price rule
     *
     * @param ProductPriceRule $priceRule
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductPriceRule $priceRule);

    /**
     * Delete product price rule by id
     *
     * @param int $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Delete product price rule by array of id
     *
     * @param array $ids
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByIds($ids);

    /**
     * Get related product by rule id, product id
     *
     * @param int $ruleId
     * @param int $productId
     * @return \Bss\CustomPricing\Model\ProductPrice
     */
    public function getBy($ruleId, $productId);

    /**
     * Check if product has applied in rule
     *
     * @param int $ruleId
     * @param int $productId
     * @return bool|string
     */
    public function hasProduct($ruleId, $productId);
}
