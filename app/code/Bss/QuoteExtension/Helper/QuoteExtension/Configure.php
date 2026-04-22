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
 * Class Configure
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 */
class Configure
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $productView;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
        \Magento\Catalog\Helper\Product\View $productView,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DataObjectFactory $dataObject
    ) {
        $this->productView = $productView;
        $this->logger = $logger;
        $this->dataObject = $dataObject;
    }

    /**
     * @return \Magento\Catalog\Helper\Product\View
     */
    public function getProductView()
    {
        return $this->productView;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
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
