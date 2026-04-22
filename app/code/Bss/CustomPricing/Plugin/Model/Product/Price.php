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
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Plugin\Model\Product;

use Bss\CustomPricing\Helper\GetFinalProductPriceCustom;
use Magento\Catalog\Model\Product;

/**
 * Class Price
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Price
{
    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    protected $helperRule;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice
     */
    protected $indexPrice;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var GetFinalProductPriceCustom
     */
    protected $getFinalProductPriceCustom;

    /**
     * Price constructor.
     *
     * @param \Bss\CustomPricing\Helper\Data $helperData
     * @param \Bss\CustomPricing\Helper\CustomerRule $helperRule
     * @param \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice $indexPrice
     * @param \Psr\Log\LoggerInterface $logger
     * @param GetFinalProductPriceCustom $getFinalProductPriceCustom
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\CustomerRule $helperRule,
        \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice $indexPrice,
        \Psr\Log\LoggerInterface $logger,
        GetFinalProductPriceCustom $getFinalProductPriceCustom
    ) {
        $this->helperData = $helperData;
        $this->helperRule = $helperRule;
        $this->indexPrice = $indexPrice;
        $this->logger = $logger;
        $this->getFinalProductPriceCustom = $getFinalProductPriceCustom;
    }

    /**
     * Modify price product if product have rule
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param float $result
     * @param Product $product
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPrice(
        $subject,
        $result,
        $product
    ) {
        $ruleIds = $this->validateRule();
        if ($ruleIds) {
            $productId = $product->getId();
            $customerGroup = $this->helperRule->getCustomerSession()->getCustomerGroupId();
            $priceRule = $this->getFinalProductPriceCustom
                ->getInfoPrices($ruleIds, $productId, $customerGroup);
            if ($priceRule) {
                $product->setBssCustomPrice(true);
                return $priceRule;
            }
        }
        return $result;
    }

    /**
     * Modify price when add to cart
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param callable $proceed
     * @param Product $product
     * @param float|null $qty
     * @return mixed
     */
    public function aroundGetBasePrice(
        $subject,
        callable $proceed,
        $product,
        $qty
    ) {
        try {
            $ruleIds = $this->validateRule();
            if ($ruleIds) {
                $productId = $product->getId();
                $customerGroup = $this->helperRule->getCustomerSession()->getCustomerGroupId();
                $priceRule = $this->getFinalProductPriceCustom
                    ->getInfoPrices($ruleIds, $productId, $customerGroup);
                if (!$priceRule) {
                    return $proceed($product, $qty);
                }

                $finalPrice = $this->getFinalProductPriceCustom->getFinalPriceCustom(
                    $subject,
                    $product,
                    $priceRule,
                    $qty
                );
                return $finalPrice;
            }
            return $proceed($product, $qty);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $proceed($product, $qty);
        }
    }

    /**
     * Get ruleids
     *
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validateRule()
    {
        $ruleIds = $this->helperRule->getAppliedRules();
        $isEnable = $this->helperData->isEnabled();
        if ($ruleIds && $isEnable) {
            return $ruleIds;
        }
        return false;
    }
}
