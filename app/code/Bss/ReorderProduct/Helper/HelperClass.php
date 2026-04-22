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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Helper;

/**
 * Class HelperClass
 *
 * @package Bss\ReorderProduct\Helper
 */
class HelperClass
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * {@inheritdoc}
     */
    protected $orders;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory
     */
    protected $stockStatusCriteriaFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * HelperClass constructor.
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->customerSession = $customerSession;
        $this->orderConfig = $orderConfig;
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->serializer = $serializer;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Return result json class
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function returnResultJsonFactory()
    {
        return $this->resultJsonFactory;
    }

    /**
     * Return customer session class
     *
     * @return \Magento\Customer\Model\SessionFactory
     */
    public function returnCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Return order config class
     *
     * @return \Magento\Sales\Model\Order\Config
     */
    public function returnOrderConfig()
    {
        return $this->orderConfig;
    }

    /**
     * Return stock registry class
     *
     * @return \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public function returnStockRegistry()
    {
        return $this->stockRegistry;
    }

    /**
     * Return stock status criteria class
     *
     * @return \Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory
     */
    public function returnStockStatusCriteriaFactory()
    {
        return $this->stockStatusCriteriaFactory;
    }

    /**
     * Return serializer class
     *
     * @return \Magento\Framework\Serialize\Serializer\Serialize
     */
    public function returnSerializer()
    {
        return $this->serializer;
    }

    /**
     * Return json helper class
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function returnJsonHelper()
    {
        return $this->jsonHelper;
    }
}
