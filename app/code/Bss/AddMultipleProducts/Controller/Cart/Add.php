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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart
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
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    protected $localizedToNormalized;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolverInterface;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Locale\ResolverInterface $resolverInterface
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
        \Magento\Framework\Filter\LocalizedToNormalized $localizedToNormalized,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Locale\ResolverInterface $resolverInterface,
        \Magento\Framework\Escaper $escaper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->layout = $layout;
        $this->localizedToNormalized = $localizedToNormalized;
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resolverInterface = $resolverInterface;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $info_mn_cart = [];
        $before_cart = $this->cart;
        $info_mn_cart['qty'] = $before_cart->getItemsQty();
        $info_mn_cart['subtotal'] = $before_cart->getQuote()->getSubtotal();
        $params = $this->getRequest()->getParams();
        $result = [];
        try {
            if (isset($params['qty'])) {
                $filter = $this->localizedToNormalized->setOptions(
                    ['locale' => $this->resolverInterface->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            if (!$product) {
                return $this->_goBack();
            }

            $this->cart->addProduct($product, $params);

            $relatedAdded = false;
            if (!empty($related)) {
                $relatedAdded = true;
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $after_cart = $this->cart;
                    $info_mn_cart['qty'] = $after_cart->getItemsQty();
                    $info_mn_cart['subtotal'] = $after_cart->getQuote()->getSubtotal();
                    $cartItem = $this->cart->getQuote()->getItemByProduct($product);
                    $html = $this->getContentPopup($product, $info_mn_cart, $cartItem);
                    $result['popup'] = $html;
                    $this->messageManager->addComplexSuccessMessage(
                        'addCartSuccessMessage',
                        [
                            'product_name' => $product->getName(),
                            'cart_url' => $this->getCartUrl(),
                        ]
                    );
                    return $resultJson->setData($result);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->escaper->escapeHtml($e->getMessage())
                );
                $product_fail[$product->getId()] = ['qty' => $params['qty'], 'mess' => $e->getMessage()];
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->escaper->escapeHtml($message)
                    );
                }
                $product_fail[$product->getId()] = ['qty' => $params['qty'], 'mess' => end($messages)];
            }

            $product_poup['errors'] = $product_fail;
            $html = $this->getContentPopup($product_poup, $info_mn_cart);
            $message = __(
                'You added %1 to your shopping cart.',
                $product->getName()
            );
            $result['popup'] = $html;
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->logger->critical($e);
            return $resultJson->setData($result);
        }
    }

    /**
     * @param $product
     * @param $info_mn_cart
     * @param null $cartItem
     * @return mixed
     */
    protected function getContentPopup($product, $info_mn_cart, $cartItem = null)
    {
        $template = 'Bss_AddMultipleProducts::popup.phtml';
        $html = $this->layout
            ->createBlock(\Bss\AddMultipleProducts\Block\OptionProduct::class)
            ->setTemplate($template)
            ->setProduct($product)
            ->setCart($info_mn_cart)
            ->setTypeadd('single');
        if ($cartItem) {
            $html->setPrice($cartItem->getPrice());
        }

        return $html->toHtml();
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}
