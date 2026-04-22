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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model;

use Bss\HidePrice\Model\Attribute\Source\HidePriceCustomer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class HidePrice
{
    /**
     * @var $customerGroupId
     */
    protected $customerGroupId;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $config
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        Config $config
    ) {
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
    }

    /**
     * Active Hide Price
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $customerId
     * @param int $customerGroup
     * @param mixed $storeId
     * @param bool $isChild
     * @param bool $cusGroupId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function activeHidePrice(
        $product,
        $customerId = null,
        $customerGroup = null,
        $storeId = null,
        $isChild = false,
        $cusGroupId = false
    ) {
        if ($this->config->isEnable($storeId)) {
            if ($isChild) {
                $product = $this->productRepository->getById($product->getId());
            }
            if ($product->getHidepriceAction() == -1) { // product disabled
                return false;
            } elseif ($product->getHidepriceAction() == 0 || $product->getHidepriceAction() == '') { // global config
                if ($cusGroupId) {
                    $this->customerGroupId = $cusGroupId;
                }
                return $this->hidePriceCustomersGroupGlobal($product, $customerId, $customerGroup);
            } else { // product config
                if ($cusGroupId) {
                    $this->customerGroupId = $cusGroupId;
                }
                //product not set customer group
                if (!$this->hidePriceCustomersGroupProduct($product, $customerId, $customerGroup)) {
                    return false;
                } else { // check product setting
                    if ($this->hidePriceCustomersGroupProduct($product, $customerId, $customerGroup)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } else { // disabled
            return false;
        }
    }

    /**
     * Is enable hide price global config
     *
     * @param object $product
     * @param int $customerId
     * @param int $customerGroup
     * @return bool|int
     */
    public function hidePriceCustomersGroupGlobal($product, $customerId, $customerGroup)
    {
        $checkSpecificCustomerApply = $this->checkSpecificCustomerApply($customerId);
        if ($checkSpecificCustomerApply != HidePriceCustomer::BSS_HIDE_PRICE_USE_CONFIG) {
            return $checkSpecificCustomerApply;
        }

        $hidePriceCategories = $this->filterArray($this->config->getHidePriceCategories());
        $hidePriceCustomers = $this->filterArray($this->config->getHidePriceCustomers());

        $productCategories = $product->getCategoryIds() ? array_filter($product->getCategoryIds()) : [];
        if (empty($hidePriceCustomers)
            || !in_array($customerGroup, $hidePriceCustomers)
            || empty(array_intersect($productCategories, $hidePriceCategories))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Filter Array
     *
     * @param string $string
     * @return array
     */
    public function filterArray($string)
    {
        $newArray = [];
        if ($string != null) {
            $array = explode(',', $string);
            $newArray = array_filter($array, function ($value) {
                return $value !== '';
            });
        }
        return $newArray;
    }

    /**
     * Get value attribute account login
     *
     * @param int $customerId
     * @return string|int
     */
    public function checkSpecificCustomerApply($customerId)
    {
        if (!$customerId) {
            return HidePriceCustomer::BSS_HIDE_PRICE_USE_CONFIG;
        }
        $getAttributeHidePrice = $this->getAttributeHidePrice($customerId);
        if ($getAttributeHidePrice === null) {
            return HidePriceCustomer::BSS_HIDE_PRICE_USE_CONFIG;
        }
        return $getAttributeHidePrice->getValue();
    }

    /**
     * Get value customer attribute
     *
     * @param int $customerId
     * @return string
     */
    public function getAttributeHidePrice($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $customer->getCustomAttribute('bss_hide_pice_apply_customer');
    }

    /**
     * Check hide price for customer group
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $customerId
     * @param int $customerGroup
     * @return bool|int
     */
    public function hidePriceCustomersGroupProduct($product, $customerId, $customerGroup)
    {
        $checkSpecificCustomerApply = $this->checkSpecificCustomerApply($customerId);
        if ($checkSpecificCustomerApply != HidePriceCustomer::BSS_HIDE_PRICE_USE_CONFIG) {
            return $checkSpecificCustomerApply;
        }

        $hidePriceCustomersGroupProduct = $this->filterArray($product->getHidepriceCustomergroup());
        if (!empty($hidePriceCustomersGroupProduct)
            && count($hidePriceCustomersGroupProduct) == 1
            && $hidePriceCustomersGroupProduct[0] == -1) {
            return false;
        }

        if (!empty($hidePriceCustomersGroupProduct)
            && in_array($customerGroup, $hidePriceCustomersGroupProduct)) {
            return true;
        }
        return false;
    }
}
