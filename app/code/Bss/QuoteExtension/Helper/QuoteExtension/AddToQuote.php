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

/**
 * Class MoveToQuote
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 */
class AddToQuote
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    protected $localizedToNormalized;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolver;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\QuoteExtension\Helper\Json
     */
    protected $helperJson;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObject;

    /**
     * AddToQuote constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\QuoteExtension\Helper\Json $helperJson
     * @param \Magento\Framework\DataObjectFactory $dataObject
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Psr\Log\LoggerInterface $logger,
        \Bss\QuoteExtension\Helper\Json $helperJson,
        \Magento\Framework\DataObjectFactory $dataObject
    ) {
        $this->productRepository = $productRepository;
        $this->localizedToNormalized = $localizedToNormalized;
        $this->resolver = $resolver;
        $this->escaper = $escaper;
        $this->cartHelper = $cartHelper;
        $this->logger = $logger;
        $this->helperJson = $helperJson;
        $this->dataObject = $dataObject;
    }

    /**
     * Get product by id
     *
     * @param int $productId
     * @param int $storeId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductById($productId, $storeId)
    {
        return $this->productRepository->getById($productId, false, $storeId);
    }

    /**
     * Get Localized Fillter
     *
     * @return \Zend_Filter_LocalizedToNormalized
     */
    public function getLocalized()
    {
        return $this->localizedToNormalized->setOptions(
            ['locale' => $this->resolver->getLocale()]
        );
    }

    /**
     * Format Escape Html
     *
     * @param string $mess
     * @return array|string
     */
    public function formatEscapeHtml($mess)
    {
        return $this->escaper->escapeHtml($mess);
    }

    /**
     * Get cart url
     *
     * @return string
     */
    public function getCartUrl()
    {
        return $this->cartHelper->getCartUrl();
    }

    /**
     * Return Logger class
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function returnLoggerClass()
    {
        return $this->logger;
    }

    /**
     * Json encode data result
     *
     * @param string $result
     * @return string
     */
    public function jsonEncodeResult($result)
    {
        return $this->helperJson->serialize($result);
    }

    /**
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function createObject($data)
    {
        $object = $this->dataObject->create();
        $object->setData($data);
        return $object;
    }
}
