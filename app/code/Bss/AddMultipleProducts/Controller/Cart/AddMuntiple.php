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
 * @package    Bss_AddMultipleProducts
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AddMultipleProducts\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class AddMuntiple
 *
 * @package Bss\AddMultipleProducts\Controller\Cart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddMuntiple extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * AddMuntiple constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Psr\Log\LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Framework\Escaper $escaper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->layout = $layout;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->objectFactory = $objectFactory;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * @param $productId
     * @param $input
     * @return string
     */
    protected function checkPost($productId, $input)
    {
        $post = $this->getRequest()->getPost();
        if ($post && $post[$productId . '_' . $input]) {
            return $post[$productId . '_' . $input];
        }
        return '';
    }

    /**
     * @return array
     */
    protected function getProductIds()
    {
        $productIds = [];
        $params= $this->getRequest()->getParams();
        if ($this->getRequest()->getPost('product-select')) {
            $productIds = $this->getRequest()->getPost('product-select');
        }
        if(isset($params["popup"])) {
            return $productIds;
        }
        $data = [];
        if(is_array($productIds)) {
            foreach ($productIds as $productId) {
                if(!isset($params[$productId . "_qty-hide-price"])) {
                    $data[] = $productId;
                }
            }
        }
        return $data;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $addedProducts = [];
        $product_poup = [];
        $params = $this->getRequest()->getParams();
        $productIds = $this->getProductIds();
        $storeId = $this->_storeManager->getStore()->getId();
        foreach ($productIds as $productId) {
            $product = $this->productRepository->getById($productId, false, $storeId);
            $qty = $this->getQty($product, $productId);
            try {
                if ($qty <= 0 || !$product) {
                    continue;
                }

                // nothing to add
                $params['product'] = $productId;
                $params['qty'] = $qty;
                $params['bundle_option'] = $this->checkPost($productId, 'bundle_option');
                $params['bundle_option_qty'] = $this->checkPost($productId, 'bundle_option_qty');
                $params['super_attribute'] = $this->checkPost($productId, 'super_attribute');
                $params['super_group'] = $this->checkPost($productId, 'super_group');
                $params['options'] = $this->checkPost($productId, 'options');
                $params['links'] = $this->checkPost($productId, 'links');
                $this->cart->addProduct($product, $params);
                $related = $this->getRequest()->getParam('related_product');

                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );
                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $addedProducts = $this->getAddedProduct($product, $addedProducts, $params);
                    }
                }
                $result["addCartSuccess"] = 1;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $product_poup['errors'][$product->getId()] = ['qty' => $qty, 'mess' => $e->getMessage()];
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    $product_poup['errors'][$product->getId()] = ['qty' => $qty, 'mess' => end($messages)];
                }
                $cartItem = $this->cart->getQuote()->getItemByProduct($product);
                if ($cartItem) {
                    $this->cart->getQuote()->deleteItem($cartItem);
                }
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                $this->logger->critical($e);
            }
        }

        $html = $this->returnCart($addedProducts, $product_poup);

        $result['popup'] = $html;
        return $resultJson->setData($result);
    }

    /**
     * @param $addedProducts
     * @param $product_poup
     * @return mixed
     */
    protected function returnCart($addedProducts, $product_poup)
    {
        $errormessageCart = '';
        $info_mn_cart = [];
        $before_cart = $this->cart;
        $info_mn_cart['qty'] = $before_cart->getItemsQty();
        $info_mn_cart['subtotal'] = $before_cart->getQuote()->getSubtotal();
        if ($addedProducts) {
            try {
                $this->cart->save()->getQuote()->collectTotals();
                if (!$this->cart->getQuote()->getHasError()) {
                    $products = [];
                    foreach ($addedProducts as $product) {
                        $_item = $this->cart->getQuote()->getItemByProduct($product);
                        $product_poup['success'][] = ['id' => $product->getId(), 'price' => $_item->getPrice()];
                        $products[] = '"' . $product->getName() . '"';
                    }
                    $after_cart = $this->cart;
                    $info_mn_cart['qty'] = $after_cart->getItemsQty();
                    $info_mn_cart['subtotal'] = $after_cart->getQuote()->getSubtotal();
                    $this->messageManager->addSuccess(
                        __('%1 product(s) have been added to shopping cart: %2.', count($addedProducts), join(', ', $products))
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNotice(
                        $this->escaper->escapeHtml($e->getMessage())
                    );
                } else {
                    $errormessage = array_unique(explode("\n", $e->getMessage()));
                    $errormessageCart = end($errormessage);
                    $this->messageManager->addError(
                        $this->escaper->escapeHtml($errormessageCart)
                    );
                }

                $product_poup['success'] = [];
                foreach ($addedProducts as $product) {
                    $product_poup['errors'][$product->getId()] = ['qty' => $this->getRequest()
                        ->getPost($product->getId() . '_qty', 0), 'mess' => ''];
                }
            }
        }

        $template = 'Bss_AddMultipleProducts::popup.phtml';
        return $this->layout
            ->createBlock(\Bss\AddMultipleProducts\Block\OptionProduct::class)
            ->setTemplate($template)
            ->setProduct($product_poup)
            ->setCart($info_mn_cart)
            ->setErrorMessageCart($errormessageCart)
            ->setTypeadd('muntiple')
            ->toHtml();
    }

    /**
     * @param $product
     * @param $addedProducts
     * @param $params
     */
    protected function getAddedProduct($product, $addedProducts, $params)
    {
        if ($product->getTypeId() != 'grouped') {
            $addedProducts[] = $product;
        } else {
            $request = $this->objectFactory->create();
            $request->setData($params);
            $addedProducts = $this
                ->getChildProductGroupId($product, $addedProducts, $request);
        }

        return $addedProducts;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $addedProducts
     * @param $request
     * @return array
     */
    protected function getChildProductGroupId(\Magento\Catalog\Model\Product $product, $addedProducts, $request)
    {

        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL;
        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        foreach ($cartCandidates as $candidate) {
            $addedProducts[] = $candidate;
        }

        return $addedProducts;
    }

    /**
     * @param $product
     * @param $productId
     * @return int
     */
    protected function getQty($product, $productId)
    {
        if ($product->getTypeId() != 'grouped') {
            $qty = $this->getRequest()->getPost($productId . '_qty', 0);
        } else {
            $qty = 1;
        }
        return $qty;
    }
}
