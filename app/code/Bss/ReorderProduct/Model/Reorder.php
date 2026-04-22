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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ReorderProduct\Model;

use Bss\ReorderProduct\Helper\Data;
use Bss\ReorderProduct\Helper\HelperClass;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Reorder
{
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var null
     */
    protected $orders;

    /**
     * @var ItemFactory
     */
    protected $orderItem;

    /**
     * @var ResourceModel\OrderItem\CollectionFactory
     */
    protected $orderItemCollection;

    /**
     * @var StockStatusRepositoryInterface
     */
    protected $stockStatusRepository;

    /**
     * @var HelperClass
     */
    protected $helperClass;

    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var null
     */
    protected $item;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * ReorderProduct constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param ItemFactory $orderItem
     * @param ResourceModel\OrderItem\CollectionFactory $orderItemCollection
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param HelperClass $helperClass
     * @param PriceCurrency $priceCurrency
     * @param FilterManager $filterManager
     */
    public function __construct(
        StoreManagerInterface                     $storeManager,
        Data                                      $helper,
        CollectionFactory                         $orderCollectionFactory,
        ItemFactory                               $orderItem,
        ResourceModel\OrderItem\CollectionFactory $orderItemCollection,
        StockStatusRepositoryInterface            $stockStatusRepository,
        HelperClass                               $helperClass,
        PriceCurrency                             $priceCurrency,
        FilterManager                             $filterManager
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helper = $helper;
        $this->orderItem = $orderItem;
        $this->orderItemCollection = $orderItemCollection;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->helperClass = $helperClass;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->filterManager = $filterManager;
    }

    /**
     * Get stock by product id
     *
     * @param int $productId
     * @return StockItemInterface|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStock($productId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $criteria = $this->helperClass->returnStockStatusCriteriaFactory()->create();
        $criteria->setProductsFilter($productId);
        $criteria->addFilter('website_id', 'website_id', 0);
        $result = $this->stockStatusRepository->getList($criteria);
        $stockStatus = current($result->getItems());
        if (!$stockStatus) {
            $stockStatus = $this->helperClass->returnStockRegistry()->getStockItem($productId, $websiteId);
        }
        return $stockStatus;
    }

    /**
     * Get min sale qty
     *
     * @param Item $item
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getMinSaleQty($item)
    {
        $productId = $item->getProductId();
        if ($item->getProductType() == 'configurable' && $this->getChildProduct($item) != null) {
            $productId = $this->getChildProduct($item)->getId();
        }
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $stockItem = $this->helperClass->returnStockRegistry()->getStockItem($productId, $websiteId);
        return $stockItem->getMinSaleQty() ? $stockItem->getMinSaleQty() : 1;
    }

    /**
     * Get order collection
     *
     * @return CollectionFactory
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * Get Orders
     *
     * @return bool|array
     */
    public function getOrders()
    {
        if (!($customerId = $this->helperClass->returnCustomerSession()->create()->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                'entity_id'
            )->addFieldToFilter(
                'status',
                ['in' => $this->helperClass->returnOrderConfig()->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        if ($this->orders->getSize()) {
            return $this->orders->getAllIds();
        }

        return false;
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
     * Avaiable orders config array
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        $sort = [
            'name' => '2',
            'price' => '3',
            'qty_ordered' => '5',
            'created_at' => '6',
            'stock_status' => '7'
        ];
        return $sort;
    }

    /**
     * Get order items collection
     *
     * @return mixed
     */
    public function getItems()
    {
        $_orders = $this->getOrders();
        $collection = $this->orderItemCollection->create();
        $collection->filterOrderIds($_orders);
        return $collection;
    }

    /**
     * Get show items per page value
     *
     * @return array
     */
    public function getListperpagevalue()
    {
        $item_per_page = array_combine(
            explode(',', $this->helper->getListperpagevalue()),
            explode(',', $this->helper->getListperpagevalue())
        );
        if ($this->helper->showAlllist()) {
            $item_per_page['-1'] = 'All';
        }
        return $item_per_page;
    }

    /**
     * Get product id in item
     *
     * @param Item $item
     * @return mixed
     */
    public function getProductId($item)
    {
        $productId = $item->getProductId();
        $itemOptions = $this->helperClass->returnSerializer()->serialize($item->getReorderItemOptions());
        if ($item->getProductType() == 'configurable' && isset($itemOptions['product'])) {
            $productId = $itemOptions['product'];
        }
        if ($item->getProductType() == 'grouped' && isset($itemOptions['super_product_config']['product_id'])) {
            $productId = $itemOptions['super_product_config']['product_id'];
        }
        return $productId;
    }

    /**
     * Get child product
     *
     * @param Item $item
     * @return null
     */
    public function getChildProduct($item)
    {
        $product = null;
        if ($item->getProductType() == 'configurable') {
            $collection = $this->orderItem->create()->getCollection();
            $collection->addFieldToFilter('parent_item_id', $item->getId());
            $collection->addAttributeToSelect('product_id');
            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $product = $item->getProduct();
                    break;
                }
            }
        }
        return $product;
    }

    /**
     * @param $amount
     * @param $store
     * @return string
     */
    public function formatPrice($amount, $store)
    {
        return $this->priceCurrency->format(
            $amount,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );
    }

    /**
     * Set Item
     *
     * @param Item $item
     * @return Item
     */
    public function setItem($item)
    {
        if ($item instanceof Item) {
            $this->item = $item;
        }
        return $this->item;
    }

    /**
     * Get item
     *
     * @return Item |mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Get default order config
     *
     * @return mixed
     */
    public function getOrderDefault()
    {
        $sortby = $this->getAvailableOrders();
        return $sortby[$this->helper->getSortby()];
    }
}
