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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Helper;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class HelperClass
 * @package Bss\QuoteExtension\Helper
 */
class HelperClass
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    protected $localizedToNormalized;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolverInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerInterface;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $helperCart;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * HelperClass constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized
     * @param \Magento\Framework\Locale\ResolverInterface $resolverInterface
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Checkout\Helper\Cart $helperCart
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized,
        \Magento\Framework\Locale\ResolverInterface $resolverInterface,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Checkout\Helper\Cart $helperCart,
        \Magento\Framework\Escaper $escaper,
        \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        ProductMetadataInterface $productMetadata
    ) {
        $this->productFactory = $productFactory;
        $this->localizedToNormalized = $localizedToNormalized;
        $this->resolverInterface = $resolverInterface;
        $this->loggerInterface = $loggerInterface;
        $this->helperCart = $helperCart;
        $this->escaper = $escaper;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function returnProductFactory()
    {
        return $this->productFactory;
    }

    /**
     * @return \Magento\Framework\Filter\LocalizedToNormalized
     */
    public function returnLocalizedToNormalized()
    {
        return $this->localizedToNormalized;
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function returnResolverInterface()
    {
        return $this->resolverInterface;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function returnLoggerInterface()
    {
        return $this->loggerInterface;
    }

    /**
     * @return \Magento\Checkout\Helper\Cart
     */
    public function returnHelperCart()
    {
        return $this->helperCart;
    }

    /**
     * @return \Magento\Framework\Escaper
     */
    public function returnEscaper()
    {
        return $this->escaper;
    }

    /**
     * @return \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    public function returnStockItemCriteriaFactory()
    {
        return $this->stockItemCriteriaFactory;
    }

    /**
     * @return \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    public function returnStockItemRepository()
    {
        return $this->stockItemRepository;
    }

    /**
     * @return ProductMetadataInterface
     */
    public function returnProductMetadata()
    {
        return $this->productMetadata;
    }
}
