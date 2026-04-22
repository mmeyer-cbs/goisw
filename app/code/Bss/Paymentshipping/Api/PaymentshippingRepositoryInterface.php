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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Paymentshipping\Api;

use Bss\Paymentshipping\Api\Data\PaymentshippingInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface PaymentshippingRepositoryInterface
 *
 * @package Bss\Paymentshipping\Api
 */
interface PaymentshippingRepositoryInterface
{
    /**
     * Save payment shipping
     *
     * @param \Bss\Paymentshipping\Api\Data\PaymentshippingInterface $paymentShipping
     * @return \Bss\Paymentshipping\Api\Data\PaymentshippingInterface
     * @throws CouldNotSaveException
     */
    public function save(array $paymentShipping);

    /**
     * Get list store credit
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bss\Paymentshipping\Api\PaymentshippingSearchResultsInterface|SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get list payment
     *
     * @param string $type
     * @param int $customerGroupId
     * @return \Bss\Paymentshipping\Api\Data\PaymentshippingInterface[]|PaymentshippingSearchResultsInterface|ExtensibleDataInterface[]|SearchResultsInterface
     */
    public function getListPaymentShipping($type, $customerGroupId);

    /**
     * Add or edit payment shipping
     *
     * @param \Bss\Paymentshipping\Api\Data\PaymentshippingInterface[] $paymentShippings
     * @return boolean
     */
    public function savePaymentShippings($paymentShippings);

    /**
     * Check allow payment or shipping of customer group
     *
     * @param string $type
     * @param int $websiteId
     * @param string $method
     * @param int $customerGroupId
     * @param int $storeId
     * @return boolean
     */
    public function checkAllow($type, $websiteId, $method, $customerGroupId, $storeId);
}
