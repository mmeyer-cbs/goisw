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
namespace Bss\QuoteExtension\Helper\QuoteExtension;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class MoveToQuote
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 */
class MoveToQuote
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * MoveToQuote constructor.
     *
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerCart $cart
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helperData,
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        CustomerCart $cart
    ) {
        $this->helperData = $helperData;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
    }

    /**
     * Check product active request4quote
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isActiveRequest4Quote($product)
    {
        return $this->helperData->isActiveRequest4Quote($product);
    }

    /**
     * Get Quote Repository
     *
     * @return CartRepositoryInterface
     */
    public function getQuoteRepository()
    {
        return $this->quoteRepository;
    }

    /**
     * Get customer cart
     *
     * @return CustomerCart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Get Product Repository
     *
     * @return ProductRepositoryInterface
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }
}
