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
namespace Bss\ReorderProduct\Block;

use Bss\ReorderProduct\Model\Reorder;

/**
 * Class ReorderProduct
 *
 * @package Bss\ReorderProduct\Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Reorder
     */
    protected $modelReorder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Bss\ReorderProduct\Helper\Data
     */
    protected $helper;

    /**
     * {@inheritdoc}
     */
    protected $orders;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItem;

    /**
     * @var \Bss\ReorderProduct\Model\ResourceModel\OrderItem\CollectionFactory
     */
    protected $orderItemCollection;

    /**
     * @var \Magento\CatalogInventory\Api\StockStatusRepositoryInterface
     */
    protected $stockStatusRepository;

    /**
     * @var \Bss\ReorderProduct\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    protected $priceCurrency;

    protected $item;

    /**
     * ReorderProduct constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ReorderProduct\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItem
     * @param \Bss\ReorderProduct\Model\ResourceModel\OrderItem\CollectionFactory $orderItemCollection
     * @param \Magento\CatalogInventory\Api\StockStatusRepositoryInterface $stockStatusRepository
     * @param \Bss\ReorderProduct\Helper\HelperClass $helperClass
     * @param \Magento\Directory\Model\PriceCurrency $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Bss\ReorderProduct\Model\Reorder $modelReorder,
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ReorderProduct\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItem,
        \Bss\ReorderProduct\Model\ResourceModel\OrderItem\CollectionFactory $orderItemCollection,
        \Magento\CatalogInventory\Api\StockStatusRepositoryInterface $stockStatusRepository,
        \Bss\ReorderProduct\Helper\HelperClass $helperClass,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        array $data = []
    ) {
        $this->modelReorder = $modelReorder;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helper = $helper;
        $this->orderItem = $orderItem;
        $this->orderItemCollection = $orderItemCollection;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->helperClass = $helperClass;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Reorder Product'));
    }

    /**
     * Get stock by product id
     *
     * @param int $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStock($productId)
    {
        return $this->modelReorder->getStock($productId);
    }

    /**
     * Get min sale qty
     *
     * @param   Item $item
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMinSaleQty($item)
    {
        return $this->modelReorder->getMinSaleQty($item);
    }

    /**
     * Get Orders
     *
     * @return bool|array
     */
    public function getOrders($customerId = null)
    {
        return  $this->modelReorder->getOrders($customerId = null);
    }

    /**
     * Get media base url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Truncate String
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string $remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        return $this->filterManager->truncate(
            $value,
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }

    /**
     * Format option value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $remainder = '';
        $value = $this->truncateString($value, 55, '', $remainder);
        $result = ['value' => nl2br($value), 'remainder' => nl2br($remainder)];

        return $result;
    }

    /**
     * Avaiable orders config array
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        return $this->modelReorder->getAvailableOrders();
    }

    /**
     * Get default order config
     *
     * @return mixed
     */
    public function getOrderDefault()
    {
        return $this->modelReorder->getOrderDefault();
    }

    /**
     * Get order items collection
     *
     * @return mixed
     */
    public function getItems($customerId = null)
    {
        return $this->modelReorder->getItems($customerId=null);
    }

    /**
     * Get show items per page value
     *
     * @return array
     */
    public function getListperpagevalue()
    {
        return $this->modelReorder->getListperpagevalue();
    }

    /**
     * Get show items per page
     *
     * @return mixed
     */
    public function getListperpage()
    {
        return $this->helper->getListperpage();
    }

    /**
     * Get product id in item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return mixed
     */
    public function getProductId($item)
    {
        return $this->modelReorder->getProductId($item);
    }

    /**
     * Get child product
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return null
     */
    public function getChildProduct($item)
    {
        return $this->modelReorder->getChildProduct($item);
    }

    /**
     * Get helper Bss
     *
     * @return \Bss\ReorderProduct\Helper\Data
     */
    public function getBssHelperData()
    {
        return $this->helper;
    }

    /**
     * Format price by store
     *
     * @param float $amount
     * @param int $store
     * @return float|string
     */
    public function formatPrice($amount, $store)
    {
        return $this->modelReorder->formatPrice($amount, $store);
    }

    /**
     * Check can show button reorder product
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function canShowButtonReorder($item)
    {
        $product = $item->getProduct();
        if ($product->getIsSalable()) {
            return true;
        }
        return false;
    }

    /**
     * Set Item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Sales\Model\Order\Item
     */
    public function setItem($item)
    {
        return $this->modelReorder->setItem($item);
    }

    /**
     * Get item
     *
     * @return \Magento\Sales\Model\Order\Item |mixed
     */
    public function getItem()
    {
        return $this->modelReorder->getItem();
    }
}
