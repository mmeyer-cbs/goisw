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
namespace Bss\ReorderProduct\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Add
 *
 * @package Bss\ReorderProduct\Controller\Cart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * {@inheritdoc}
     */
    protected $orders;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var \Bss\ReorderProduct\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Escaper $escaper
     * @param CustomerCart $cart
     * @param \Bss\ReorderProduct\Helper\HelperClass $helperClass
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Escaper $escaper,
        CustomerCart $cart,
        \Bss\ReorderProduct\Helper\HelperClass $helperClass
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->escaper = $escaper;
        $this->helperClass = $helperClass;
        parent::__construct($context);
    }

    /**
     * Get data on params
     *
     * @return mixed
     */
    private function getDataParams()
    {
        $params = $this->getRequest()->getParams();
        $itemIds = [];
        if (isset($params['type']) && $params['type'] == 'addmultiple') {
            foreach ($this->helperClass->returnJsonHelper()->jsonDecode($params['item']) as $item) {
                $itemIds[] = $item['id'];
                $params['qty_' . $item['id']] = $item['qty'];
            }
        } else {
            $itemIds[] = $params['item'];
        }
        if (($key = array_search('reorder-select-all', $itemIds)) !== false) {
            unset($itemIds[$key]);
        }
        $params['item_ids'] = $itemIds;
        return $params;
    }

    /**
     * Add to cart on reorder page function execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->helperClass->returnResultJsonFactory()->create();
        $params = $this->getDataParams();
        $addedProducts = $result = [];
        $itemIds = $params['item_ids'];
        $orders = $this->getOrders();
        $result['status'] = '';
        foreach ($orders as $order) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                $itemId = $item->getId();
                if (in_array($itemId, $itemIds)) {
                    try {
                        $qty = $this->getQty($params, $itemId);
                        if ($qty <= 0) {
                            continue;
                        }
                        $this->addOrderItem($item, $qty);

                        $addedProducts[] = $item->getProduct();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        if ($this->checkoutSession->getUseNotice(true)) {
                            $this->messageManager->addNoticeMessage($e->getMessage());
                        } else {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        }

                        $cartItem = $this->cart->getQuote()->getItemByProduct($item->getProduct());
                        if ($cartItem) {
                            $this->cart->getQuote()->deleteItem($cartItem);
                        }
                        $result['status'] = 'ERROR';
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage(
                            $e,
                            __('We can\'t add this item to your shopping cart right now.')
                        );
                        $result['status'] = 'ERROR';
                    }
                }
            }
        }
        $result['type'] = 'cart';
        $result['status'] = $this->saveCart($result['status'], $addedProducts);
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * Get item qty
     *
     * @param array $params
     * @param int $itemId
     * @return int
     */
    protected function getQty($params, $itemId)
    {
        if (isset($params['type']) && $params['type'] == 'addmultiple') {
            return $params['qty_' . $itemId];
        }
        return $params['qty'];
    }

    /**
     * Process save and return message
     *
     * @param string $result
     * @param array $addedProducts
     * @return string
     */
    private function saveCart($result, $addedProducts = null)
    {
        if ($addedProducts) {
            try {
                $this->cart->save()->getQuote()->collectTotals();
                if (!$this->cart->getQuote()->getHasError()) {
                    $products = [];
                    foreach ($addedProducts as $product) {
                        $products[] = '"' . $product->getName() . '"';
                    }
                    $this->messageManager->addSuccessMessage(
                        __(
                            '%1 product(s) have been added to shopping cart: %2.',
                            count($addedProducts),
                            join(', ', $products)
                        )
                    );
                    $result = 'SUCCESS';
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage(
                        $this->escaper->escapeHtml($e->getMessage())
                    );
                } else {
                    $errormessage = array_unique(explode("\n", $e->getMessage()));
                    $errormessageCart = end($errormessage);
                    $this->messageManager->addErrorMessage(
                        $this->escaper->escapeHtml($errormessageCart)
                    );
                }
                $result = 'ERROR';
            }
        }
        return $result;
    }

    /**
     * Add order item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param string $qty
     * @return $this
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addOrderItem($orderItem, $qty)
    {

        if ($orderItem->getParentItem() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this;
            }

            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            if ($info) {
                $info = $this->dataObjectFactory->create()->addData($info);
            } else {
                $info = $this->dataObjectFactory->create();
            }
            $info->setQty($qty);

            $this->cart->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Get orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getOrders()
    {
        if (!($customerId = $this->helperClass->returnCustomerSession()->create()->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $this->orders = $this->orderCollectionFactory->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $this->helperClass->returnOrderConfig()->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->orders;
    }
}
