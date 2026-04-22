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

namespace Bss\CustomPricing\Helper;

use Magento\Framework\App\Helper\Context;
use Bss\CustomPricing\Api\Data\ProductPriceInterface;
use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;

/**
 * Helper product save after for module
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProductSave extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * ProductSave constructor.
     * @param Context $context
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        PriceRuleRepositoryInterface $priceRuleRepository,
        ProductPriceRepositoryInterface $productPriceRepository,
        \Bss\CustomPricing\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->priceRuleRepository = $priceRuleRepository;
        $this->productPriceRepository = $productPriceRepository;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    /**
     * Get price rules
     *
     * @return \Bss\CustomPricing\Api\Data\PriceRuleSearchResultsInterface
     */
    public function getPriceRules()
    {
        $searchBuilder = $this->searchCriteriaBuilder->create();
        $priceRules = $this->priceRuleRepository->getList($searchBuilder);
        return $priceRules;
    }

    /**
     * Check and insert or update product to bss price table
     *
     * @param \Bss\CustomPricing\Api\Data\PriceRuleInterface $priceRule
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function insertOrUpdateProductPrice($priceRule, $product): int
    {
        try {
            $productPrice = $this->productPriceRepository->getBy($priceRule->getId(), $product->getId());
            if ($productPrice->isObjectNew()) {
                $productPrice->setData(
                    [
                        ProductPriceInterface::RULE_ID => $priceRule->getId(),
                        ProductPriceInterface::PRODUCT_ID => $product->getId(),
                        ProductPriceInterface::NAME => $product->getName(),
                        ProductPriceInterface::TYPE_ID => $product->getTypeId(),
                        ProductPriceInterface::ORIGIN_PRICE => $product->getPrice(),
                        ProductPriceInterface::PRODUCT_SKU => $product->getSku()
                    ]
                );
            } else {
                $productPrice->setName($product->getName());
                $productPrice->setTypeId($product->getTypeId());
                $productPrice->setOriginPrice($product->getPrice());
                $productPrice->setProductSku($product->getSku());
            }

            // Set should reindex ở đây vì func này được dùng khi update nhiều product price cùng lúc
            // vì thế set bằng false để hệ thống ko reindex sau khi lưu product price
            // Check trong function afterSave
            $productPrice->setShouldReindex(false);

            $this->processExpectedPrice($priceRule, $productPrice, $product);
            $this->productPriceRepository->save($productPrice);

            if ($this->shouldReindex($product)) {
                return (int) $productPrice->getId();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return 0;
    }

    /**
     * Process expected price
     *
     * @param $rule
     * @param \Bss\CustomPricing\Api\Data\ProductPriceInterface $productPrice
     * @param $product
     */
    protected function processExpectedPrice($rule, $productPrice, $product)
    {
        $priceType = null;
        $priceValue = null;
        if ($productPrice->getPriceValue() !== null) {
            $priceType = $productPrice->getPriceMethod();
            $priceValue = $productPrice->getPriceValue();
        } else {
            if ($rule->getDefaultPriceValue() !== null && $rule->getDefaultPriceMethod() !== null) {
                $priceType = $rule->getDefaultPriceMethod();
                $priceValue = $rule->getDefaultPriceValue();
                $productPrice->setPriceMethod($priceType);
                $productPrice->setPriceValue($priceValue);
            }
        }

        $customPrice = $this->helper->prepareCustomPrice(
            $priceType,
            $product->getPrice(),
            $priceValue
        );
        $productPrice->setCustomPrice($customPrice);
    }

    /**
     * Should reindex the product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    protected function shouldReindex(\Magento\Catalog\Model\Product $product): bool
    {
        $should = false;

        if ($this->helper->applyNormalSpecialPrice()) {
            if ((float) $product->getData('special_price') !== (float) $product->getOrigData('special_price')) {
                $should = true;
            }
        }

        if ($this->helper->applyNormalTierPrice()) {
            $tierPrice = $product->getData('tier_price');
            $orgData = $product->getOrigData('tier_price');

            if ($tierPrice === null) {
                $tierPrice = [];
            }

            if ($orgData === null) {
                $orgData = [];
            }

            if (count($tierPrice) !== count($orgData)) {
                return true;
            }

            foreach ($tierPrice as $item) {
                // New tier price
                if (!isset($item['price_id'])) {
                    return true;
                }

                foreach ($orgData as $orgItem) {
                    if ($item['price_id'] === $orgItem['price_id']) {
                        if ($item['website_id'] !== $orgItem['website_id'] ||
                            $item['all_groups'] !== $orgItem['all_groups'] ||
                            $item['cust_group'] != $orgItem['cust_group'] ||
                            (float) $item['price_qty'] !== (float) $orgItem['price_qty'] ||
                            (isset($item['price']) && (float) $item['price'] != (float) $orgItem['price']) ||
                            (
                                isset($item['percentage_value']) &&
                                $item['percentage_value'] != $orgItem['percentage_value']
                            )
                        ) {
                            return true;
                        }
                    }
                }
            }
        }

        return $should;
    }
}
