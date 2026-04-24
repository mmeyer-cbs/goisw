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
namespace Bss\QuoteExtension\Controller;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;

/**
 * Quotation Quote controller
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class Quote extends \Magento\Framework\App\Action\Action implements ViewInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\QuoteExtension\Model\Session
     */
    protected $quoteExtensionSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerQuoteExtension
     */
    protected $quoteExtension;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote
     */

    protected $manageQuote;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Quote constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->quoteExtensionSession = $quoteExtensionSession;
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->quoteExtension = $quoteExtension;
        $this->manageQuote = $manageQuote;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Getter for quoteExtensionSession
     *
     * @return \Bss\QuoteExtension\Model\Session
     */
    public function getQuoteExtensionSession()
    {
        return $this->quoteExtensionSession;
    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl) {
            $resultRedirect->setUrl($backUrl);
            return $resultRedirect;
        }
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl && strpos($refererUrl, 'customer/section/load') !== false) {
            $backUrl = $this->_url->getUrl('quoteextension/quote');
        }

        if ($backUrl || $backUrl = $this->getBackUrl($refererUrl)) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }

    /**
     * Get resolved back url
     *
     * @param null $defaultUrl
     * @return mixed|null|string
     */
    protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();

            return $returnUrl;
        }

        //use magento cart settings
        $shouldRedirectToCart = $this->scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($shouldRedirectToCart || $this->getRequest()->getParam('in_quote')) {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_quote')) {
                $this->quoteExtensionSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }

            return $this->_url->getUrl('quoteextension/quote');
        }

        return $defaultUrl;
    }

    /**
     * Check if URL corresponds store
     *
     * @param string $url
     * @return bool
     */
    protected function _isInternalUrl($url)
    {
        if ($url && strpos($url, 'http') === false) {
            return false;
        }

        /**
         * Url must start from base secure or base unsecure url
         */
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->storeManager->getStore();
        if ($url) {
            $unsecure = strpos($url, $store->getBaseUrl()) === 0;
            $secure = strpos($url, $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true)) === 0;
            return $unsecure || $secure;
        }
        return false;
    }

    /**
     * Set success redirect url to response
     *
     * @param null $successUrl
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _successPage($successUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($successUrl === null) {
            $successUrl = $this->_url->getUrl(
                'quoteextension/quote/success',
                [
                    'id' => $this->quoteExtensionSession->getLastQuoteId()
                ]
            );
        }
        $resultRedirect->setUrl($successUrl);

        return $resultRedirect;
    }

    /**
     * Checks if the request is valid
     *
     * @return bool
     */
    protected function isInvalidQuoteRequest()
    {
        $quoteId = $this->quoteExtensionSession->getQuoteExtensionId();
        $quote = $this->quoteExtensionSession->getQuoteExtension();
        return $quoteId == null || !($quote && $quote->hasItems() && $quote->getIsActive());
    }
}
