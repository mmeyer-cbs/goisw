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
namespace Bss\ReorderProduct\Controller\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Helper\Data;

/**
 * Class Add
 *
 * @package Bss\ReorderProduct\Controller\Wishlist
 */
class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    protected $wishlistData;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $orderItems;

    /**
     * @var array
     */
    private $groupProduct = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Bss\ReorderProduct\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * Add constructor.
     * @param Action\Context $context
     * @param Data $wishlistData
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Bss\ReorderProduct\Helper\HelperClass $helperClass
     */
    public function __construct(
        Action\Context $context,
        Data $wishlistData,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Bss\ReorderProduct\Helper\HelperClass $helperClass
    ) {
        $this->wishlistData = $wishlistData;
        $this->wishlistProvider = $wishlistProvider;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->helperClass = $helperClass;
        parent::__construct($context);
    }

    /**
     * Add to wishlist function execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->helperClass->returnResultJsonFactory()->create();
        $wishlist = $this->wishlistProvider->getWishlist();
        $addedProducts = [];
        $error = [];
        $items = $this->getOrderItems();
        $result = [];
        try {
            foreach ($items as $item) {
                $product = $item->getProduct();
                $buyRequest = $item->getBuyRequest();
                if ($item->getProductType() === 'grouped') {
                    $this->getGroupProduct($item);
                    continue;
                }
                if (!$product || !$product->isVisibleInCatalog()) {
                    $this->messageManager->addErrorMessage(__('We can\'t specify product id %1.'), $product->getId());
                    continue;
                }
                $addWishlist = $wishlist->addNewItem($product, $buyRequest);
                if (is_string($result)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($addWishlist));
                }
                $this->saveWishlist($wishlist);
                $this->_eventManager->dispatch(
                    'wishlist_add_product',
                    ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                );
                $addedProducts[] = $product->getName();
            }
            foreach ($this->groupProduct as $gProduct) {
                $product = $gProduct['product'];
                $buyRequest = $this->dataObjectFactory->create(['super_group' => $gProduct['super_group']]);
                $addWishlist = $wishlist->addNewItem($product, $buyRequest);
                $this->checkException($result, $addWishlist);
                $this->saveWishlist($wishlist);
                $this->_eventManager->dispatch(
                    'wishlist_add_product',
                    ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                );
                $addedProducts[] = $product->getName();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now')
            );
            $error[] = $e->getMessage();
            $result['status'] = 'ERROR';
        }
        if ($addedProducts) {
            $this->messageManager->addSuccessMessage(
                __(
                    '%1 product(s) have been added to your Wish List: %2.',
                    count($addedProducts),
                    join(', ', $addedProducts)
                )
            );
            $this->wishlistData->calculate();
            $result['status'] = 'SUCCESS';
        }
        $result['type'] = 'wishlist';
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * Throw exception if result return is string
     *
     * @param string|array $result
     * @param string $addWishlist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkException($result, $addWishlist)
    {
        if (is_string($result)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($addWishlist));
        }
    }

    /**
     * Save wishlist
     *
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @throws \Exception
     */
    protected function saveWishlist($wishlist)
    {
        $wishlist->save();
    }

    /**
     * Get order items
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    private function getOrderItems()
    {
        $items = $this->getRequest()->getParam('item', '[]');
        foreach ($this->helperClass->returnJsonHelper()->jsonDecode($items) as $item) {
            if ($item['qty']) {
                $this->params[$item['id']] = $item['qty'];
            }
        }
        if (!$this->orderItems && !empty($this->params)) {
            $this->orderItems = $this->orderItemCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'item_id',
                ['in' => array_keys($this->params)]
            )->setOrder(
                'created_at',
                'desc'
            )->getItems();
        }
        return $this->orderItems;
    }

    /**
     * Get child group product
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    private function getGroupProduct($item)
    {
        $productConfig = $item->getProductOptionByCode('super_product_config');
        if (isset($productConfig['product_id'])) {
            try {
                $superGroup = $item->getProduct()->getId();
                $product = $this->productRepository->getById($productConfig['product_id']);
                if (isset($this->groupProduct[$productConfig['product_id']])) {
                    $productId = $productConfig['product_id'];
                    $this->groupProduct[$productId]['super_group'][$superGroup] = $this->params[$item->getId()];
                } else {
                    $this->groupProduct[$productConfig['product_id']] = [
                        'super_group' => [$superGroup => $this->params[$item->getId()]],
                        'product' => $product
                    ];
                }
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }
    }
}
