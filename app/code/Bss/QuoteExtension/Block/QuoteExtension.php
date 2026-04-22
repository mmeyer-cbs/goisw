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
namespace Bss\QuoteExtension\Block;

/**
 * Class QuoteExtension
 *
 * @package Bss\QuoteExtension\Block
 */
class QuoteExtension extends \Bss\QuoteExtension\Block\QuoteExtension\AbstractQuoteExtension
{
    /**
     * Catalog Url Builder
     *
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $catalogUrlBuilder;

    /**
     * Http Context
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Cart Helper
     *
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * Customer Url
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * Visibility Enabled
     *
     * @var bool
     */
    protected $visibilityEnabled;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObject;

    /**
     * Flag for knowing if the full form is set
     *
     * @var bool
     */
    protected $fullFormSet = false;

    /**
     * QuoteExtension constructor.
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\DataObjectFactory $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\DataObjectFactory $dataObject,
        array $data = []
    ) {
        $this->cartHelper = $cartHelper;
        $this->catalogUrlBuilder = $catalogUrlBuilder;
        $this->httpContext = $httpContext;
        $this->customerUrl = $customerUrl;
        $this->dataObject = $dataObject;
        parent::__construct($context, $customerSession, $quoteExtensionSession, $data);
    }

    /**
     * Prepare Quote Item Product URLs
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->customerSession->setAfterAuthUrl($this->getUrl('quoteextension/quote'));
        $this->prepareItemUrls();
    }

    /**
     * Checks if the quote has an error
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->getQuoteExtension()->getHasError();
    }

    /**
     * Get the continue shopping URL
     *
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if ($url === null) {
            $url = $this->quoteExtensionSession->getContinueShoppingUrl(true);
            if (!$url) {
                $url = $this->_urlBuilder->getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }

        return $url;
    }

    /**
     * Get quote item count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getQuoteExtension()->getItemsCount();
    }

    /**
     * Checks if the customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get the login url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    /**
     * Get the request for quote form
     *
     * @return string
     */
    public function getForm()
    {
        $form = $this->getChildHtml('checkout.root');
        $this->fullFormSet = true;

        return $form;
    }

    /**
     * Prepare quote items URLs
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareItemUrls()
    {
        $products = $this->getProducts();

        if ($products) {
            $products = $this->catalogUrlBuilder->getRewriteByProductStore($products);
            foreach ($this->getItems() as $item) {
                $product = $item->getProduct();
                $option = $item->getOptionByCode('product_type');
                if ($option) {
                    $product = $option->getProduct();
                }

                if (isset($products[$product->getId()])) {
                    $urlDataObject = $this->dataObject->create();
                    $urlDataObject->setData($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($urlDataObject);
                }
            }
        }
    }

    /**
     * Get Products
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProducts()
    {
        $products = [];
        /** @var $item \Magento\Quote\Model\Quote\Item */
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $option = $item->getOptionByCode('product_type');
            if ($option) {
                $product = $option->getProduct();
            }

            if ($item->getStoreId() != $this->_storeManager->getStore()->getId() &&
                !$item->getRedirectUrl() &&
                !$product->isVisibleInSiteVisibility()
            ) {
                $products[$product->getId()] = $item->getStoreId();
            }
        }
        return $products;
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->getCustomItems()) {
            return $this->getCustomItems();
        }

        return parent::getItems();
    }
}
