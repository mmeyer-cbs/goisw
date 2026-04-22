<?php
declare(strict_types=1);
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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Helper;


use Bss\CustomPricing\Api\AppliedCustomersRepositoryInterface;
use Bss\CustomPricing\Api\Data\ProductPriceInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;

class PriceRuleSave
{
    /**
     * @var array
     */
    private $oldCustomer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductPriceRepositoryInterface
     */
    private $productPriceRepository;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\ProductPrice
     */
    private $productPriceResource;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $typeList;

    /**
     * @var AppliedCustomersRepositoryInterface
     */
    private $appliedCustomersRepository;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\AppliedCustomers
     */
    private $appliedCustomersResource;

    /**
     * PriceRuleSave constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param \Bss\CustomPricing\Model\ResourceModel\ProductPrice $productPriceResource
     * @param Data $helper
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param AppliedCustomersRepositoryInterface $appliedCustomersRepository
     * @param \Bss\CustomPricing\Model\ResourceModel\AppliedCustomers $appliedCustomersResource
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductPriceRepositoryInterface $productPriceRepository,
        \Bss\CustomPricing\Model\ResourceModel\ProductPrice $productPriceResource,
        \Bss\CustomPricing\Helper\Data $helper,
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        AppliedCustomersRepositoryInterface $appliedCustomersRepository,
        \Bss\CustomPricing\Model\ResourceModel\AppliedCustomers $appliedCustomersResource
    ) {
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productPriceRepository = $productPriceRepository;
        $this->productPriceResource = $productPriceResource;
        $this->helper = $helper;
        $this->typeList = $typeList;
        $this->appliedCustomersRepository = $appliedCustomersRepository;
        $this->appliedCustomersResource = $appliedCustomersResource;
    }

    /**
     * Save custom price for product by rule
     *
     * @param \Bss\CustomPricing\Model\PriceRule $rule
     * @throws CouldNotSaveException
     */
    public function saveProductPrice($rule)
    {
        try {
            // Get product data by rule's product condition
            $newProductsData = $rule->getListProductData(['name', 'sku', 'price', 'type_id']);
            // remove the product which no longer apply by new rule's product condition
            $this->searchCriteriaBuilder->addFilter('rule_id', $rule->getId(), 'eq');
            $currentProductsData = $this->productPriceRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();
            $noLongerApplyProductIds = $this->getNoLongerApplyProductPriceIds($newProductsData, $currentProductsData);

            if ($noLongerApplyProductIds) {
                $this->productPriceRepository->deleteByIds($noLongerApplyProductIds);
            }

            // apply new product by new rule's product condition
            $newApplyProductPrice = $this->getNewApplyProductPrice($newProductsData, $currentProductsData);
            $importProductPrice = [];
            foreach ($newProductsData as $productId => $data) {
                if ($rule->getDefaultPriceValue() && $rule->getDefaultPriceMethod()) {
                    $priceType = $rule->getDefaultPriceMethod();
                    $priceValue = $rule->getDefaultPriceValue();
                    $customPrice = $this->helper->prepareCustomPrice(
                        $priceType,
                        $data["price"] ?? 0,
                        $priceValue
                    );
                }
                if (in_array($productId, $newApplyProductPrice)) {
                    $importProductPrice[] = [
                        'name' => $data["name"] ?? null,
                        'type_id' => $data["type_id"] ?? null,
                        'rule_id' => $rule->getId(),
                        'origin_price' => $data["price"] ?? null,
                        'product_id' => $productId,
                        'custom_price' => $customPrice ?? null,
                        'product_sku' => $data["sku"] ?? null,
                        ProductPriceInterface::PRICE_METHOD => $priceType ?? null,
                        ProductPriceInterface::PRICE_VALUE => $priceValue ?? null
                    ];
                }
            }
            if (!empty($importProductPrice)) {
                $connection = $this->productPriceResource->getConnection();
                $table = $this->productPriceResource->getMainTable();
                $connection->insertMultiple($table, $importProductPrice);
            }
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __("Something went wrong while saving the rule data. Please review the error log.")
            );
        }
    }

    /**
     * Save applied customers by rule
     *
     * @param \Bss\CustomPricing\Model\PriceRule $rule
     * @throws CouldNotSaveException
     */
    public function saveAppliedCustomers($rule)
    {
        try {
            $newCustomersData = [];
            if ($rule->needProcessCustomers()) {
                // Get customer data by rule's customer condition
                $newCustomersData = $rule->getListCustomersDataCompatible($rule->getWebsiteId(), ['firstname', 'lastname']);
            }
            // remove the customer which no longer apply by new rule's customer condition
            $this->searchCriteriaBuilder->addFilter('rule_id', $rule->getId(), 'eq');
            $currentCustomersData = $this->appliedCustomersRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();
            $newCustomersData = $this->validateNewCustomer($rule->getId(), $newCustomersData, $currentCustomersData);
            $importCustomerData = [];
            foreach ($newCustomersData as $customerId => $data) {
                $importCustomerData[] = [
                    'customer_first_name' => $data["firstname"],
                    'customer_last_name' => $data["lastname"],
                    'customer_id' => $customerId,
                    'rule_id' => $rule->getId(),
                    'applied_rule' => true
                ];
            }
            if (!empty($importCustomerData)) {
                $connection = $this->appliedCustomersResource->getConnection();
                $table = $this->appliedCustomersResource->getMainTable();
                $connection->insertMultiple($table, $importCustomerData);
            }
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __("Something went wrong while saving the rule data. Please review the error log.")
            );
        }
    }

    /**
     * Validate new customer array
     *
     * @param int $rulesId
     * @param array $newCustomersData
     * @param array $currentCustomersData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateNewCustomer($rulesId, $newCustomersData, $currentCustomersData)
    {
        if (!empty($currentCustomersData) && is_array($currentCustomersData)) {
            foreach ($currentCustomersData as $customer) {
                $customerId = $customer->getData('customer_id');
                if (isset($newCustomersData[$customerId])) {
                    unset($newCustomersData[$customerId]);
                } else {
                    $this->oldCustomer[] = $customer->getId();
                }
            }
        }
        $this->removeOldCustomer($rulesId);
        return $newCustomersData;
    }

    /**
     * Remove no lonnger applied customers
     *
     * @param int $rulesId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function removeOldCustomer($rulesId)
    {
        if (!empty($this->oldCustomer)) {
            $this->oldCustomer = implode(',', $this->oldCustomer);
            $this->appliedCustomersResource->removeOldCustomers($rulesId, $this->oldCustomer);
        }
    }

    /**
     * Need clear cache after save rule
     * @deprecated 1.0.7 - Use \Bss\CustomPricing\Helper\Data::markInvalidateCache instead
     */
    public function markInvalidateCache()
    {
        $this->typeList->invalidate(
            [
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
                \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER
            ]
        );
    }

    /**
     * Compare and get product price ids that will be apply to the price rule
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return array
     */
    protected function getNewApplyProductPrice($newData, $oldData)
    {
        $newProductPriceIds = $this->getProductPriceIds($newData);
        $currentProductPriceIds = $this->getProductPriceIds($oldData, "old");

        return array_diff($newProductPriceIds, array_keys($currentProductPriceIds));
    }

    /**
     * Compare and get product price ids that was no longer applied to the price rule
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return array
     */
    protected function getNoLongerApplyProductPriceIds($newData, $oldData)
    {
        $newProductPriceIds = $this->getProductPriceIds($newData);
        $currentProductPriceIds = $this->getProductPriceIds($oldData, "old");
        $removeProductIds = array_diff(array_keys($currentProductPriceIds), $newProductPriceIds);
        $noLongerApplyProductIds = [];
        foreach ($currentProductPriceIds as $productId => $productPriceId) {
            if (in_array($productId, $removeProductIds)) {
                $noLongerApplyProductIds[] = $productPriceId;
            }
        }
        return $noLongerApplyProductIds;
    }

    /**
     * Mapping data product id
     *
     * @param object|array $productPrices
     * @param string $dataType
     *
     * @return array
     */
    private function getProductPriceIds($productPrices, $dataType = "new")
    {
        $data = [];
        if ($productPrices && $dataType == "old") {
            foreach ($productPrices as $productPrice) {
                $data[(int)$productPrice->getProductId()] = (int)$productPrice->getId();
            }
        } else {
            $data = array_keys($productPrices);
        }
        return $data;
    }
}
