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
namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Model\Config\Source\Status;

/**
 * Class QuoteExtensionCart
 *
 * @package Bss\QuoteExtension\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteExtensionCart extends \Magento\Checkout\Helper\Cart
{
    /**
     * Path to controller to delete item from cart
     */
    const DELETE_URL_QUOTE = 'quoteextension/quote/delete';

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Bss\QuoteExtension\Model\QuoteExtension
     */
    protected $quoteExtensionCart;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * QuoteExtensionCart constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\QuoteExtension\Model\QuoteExtension $quoteExtensionCart
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\QuoteExtension\Model\QuoteExtension $quoteExtensionCart
    ) {
        $this->checkoutCart = $quoteExtensionCart;
        $this->coreRegistry = $registry;
        $this->productRepository = $productRepository;
        parent::__construct($context, $quoteExtensionCart, $checkoutSession);
    }

    /**
     * Retrieve url for add product to quote
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return  string
     */
    public function getAddUrl($product, $additional = [])
    {
        $continueUrl = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        $urlParamName = \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $continueUrl,
            'product' => $product->getEntityId(),
            '_secure' => $this->_getRequest()->isSecure()
        ];

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_scope_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout' && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('quoteextension/quote/add', $routeParams);
    }

    /**
     * Retrieve url for add product to quote from wishlist
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return  string
     */
    public function getAddUrlWishList($product, $additional = [])
    {
        $continueUrl = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        $urlParamName = \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $continueUrl,
            'product' => $product->getEntityId(),
            '_secure' => $this->_getRequest()->isSecure()
        ];

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_scope_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout' && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('quoteextension/wishlist/add', $routeParams);
    }

    /**
     * Get post parameters for delete from quote
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return string
     */
    public function getDeletePostJson($item)
    {
        $url = $this->_getUrl(self::DELETE_URL_QUOTE);

        $data = ['id' => $item->getId()];
        if (!$this->_request->isAjax()) {
            $data[\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED] = $this->getCurrentBase64Url();
        }
        return json_encode(['action' => $url, 'data' => $data]);
    }

    /**
     * Retrieve quotation quote url
     *
     * @return string
     */
    public function getCartUrl()
    {
        return $this->_getUrl('quotation/quote');
    }

    /**
     * Can show price
     *
     * @param \Magento\Sales\Model\Item $item
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canShowSubtotal($item)
    {
        $manaQuote = $this->coreRegistry->registry('current_quote_extension');
        if ($manaQuote->getStatus() === Status::STATE_PENDING
            || $manaQuote->getStatus() === Status::STATE_CANCELED
            || $manaQuote->getStatus() === Status::STATE_REJECTED
        ) {
            $product = $item->getProduct();
            if ($item->getProduct()->getTypeId() == "configurable") {
                $product = $this->productRepository->get($item->getSku());
            }
            if ($product->getCanShowPrice() === false) {
                return false;
            }
        }
        return true;
    }

}
