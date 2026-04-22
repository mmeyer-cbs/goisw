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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Helper\Model as HelperModel;
use Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote as HelperAddToQuote;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddMultiple
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class AddA extends Action
{
    /**
     * @var HelperModel
     */
    protected $helperModel;

    /**
     * @var Session
     */
    protected $quoteExtensionSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HelperAddToQuote
     */
    protected $helperAddToQuote;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Factory
     */
    protected $objectFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LocalizedToNormalized
     */
    protected $localizedToNormalized;

    /**
     * @var ResolverInterface
     */
    protected $resolverInterface;


    /**
     * AddMultiple constructor.
     *
     * @param HelperModel $helperModel
     * @param Session $quoteExtensionSession
     * @param StoreManagerInterface $storeManager
     * @param HelperAddToQuote $helperAddToQuote
     * @param Factory $objectFactory
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param LayoutInterface $layout
     * @param JsonFactory $resultJsonFactory
     * @param ResolverInterface $resolverInterface
     * @param LocalizedToNormalized $localizedToNormalized
     * @param Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HelperModel $helperModel,
        Session $quoteExtensionSession,
        StoreManagerInterface $storeManager,
        HelperAddToQuote $helperAddToQuote,
        Factory $objectFactory,
        LoggerInterface $logger,
        Escaper $escaper,
        LayoutInterface $layout,
        JsonFactory $resultJsonFactory,
        ResolverInterface $resolverInterface,
        LocalizedToNormalized $localizedToNormalized,
        Context $context
    ) {
        $this->helperModel = $helperModel;
        $this->quoteExtensionSession = $quoteExtensionSession;
        $this->storeManager = $storeManager;
        $this->helperAddToQuote = $helperAddToQuote;
        $this->objectFactory = $objectFactory;
        $this->logger = $logger;
        $this->escaper = $escaper;
        $this->layout = $layout;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->localizedToNormalized = $localizedToNormalized;
        $this->resolverInterface = $resolverInterface;
        parent::__construct($context);
    }

    /**
     * Get Product by id
     *
     * @return false|ProductInterface
     */
    protected function getProductById()
    {
        try {
            $productId = (int)$this->getRequest()->getParam('product');
            $storeId = $this->storeManager->getStore()->getId();
            return $this->helperAddToQuote->getProductById($productId, $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Add quote with param qty > 1 in category page
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $info_mn_cart = [];
        $before_cart = $this->helperModel->quoteExtension();
        $info_mn_cart['qty'] = $before_cart->getItemsQty();
        $info_mn_cart['subtotal'] = $before_cart->getQuote()->getSubtotal();
        $params = $this->getRequest()->getParams();
        $result = [];
        try {
            if (isset($params['qty'])) {
                $filter = $this->localizedToNormalized->setOptions(
                    ['locale' => $this->resolverInterface->getLocale()]
                );
                $params["quoteextension"] = 1;
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->getProductById();
            $related = $this->getRequest()->getParam('related_product');
            $this->helperModel->quoteExtension()->addProduct($product, $params);

            if (!empty($related)) {
                $this->helperModel->quoteExtension()->addProductsByIds(explode(',', $related));
            }

            $this->helperModel->quoteExtension()->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->quoteExtensionSession->getNoCartRedirect(true)) {
                if (!$this->helperModel->quoteExtension()->getQuote()->getHasError()) {
                    $after_cart = $this->helperModel->quoteExtension();
                    $info_mn_cart['qty'] = $after_cart->getItemsQty();
                    $info_mn_cart['subtotal'] = $after_cart->getQuote()->getSubtotal();
                    if(!$this->helperModel->canShowSubtotal()) {
                        $info_mn_cart['subtotal'] = "";
                    }
                    $cartItem = $after_cart->getQuote()->getItemByProduct($product);
                    $html = $this->getContentPopup($product, $info_mn_cart, $cartItem);
                    $result['popup'] = $html;
                    $this->messageManager->addComplexSuccessMessage(
                        'addQuoteSuccessMessage',
                        [
                            'product_name' => $product->getName(),
                            'quote_url' => $this->getCartUrl(),
                        ]
                    );
                    $this->helperModel->quoteExtension()->save();
                    return $resultJson->setData($result);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->quoteExtensionSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->escaper->escapeHtml($e->getMessage())
                );
                $product_fail[$product->getId()] = ['qty' => $params['qty'], 'mess' => $e->getMessage()];
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->escaper->escapeHtml($message)
                    );
                }
                $product_fail[$product->getId()] = ['qty' => $params['qty'], 'mess' => end($messages)];
            }

            $product_poup['errors'] = $product_fail;
            if(!$this->helperModel->canShowSubtotal()) {
                $info_mn_cart['subtotal'] = "";
            }
            $html = $this->getContentPopup($product_poup, $info_mn_cart);
            $result['popup'] = $html;
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            if(!$this->helperModel->canShowSubtotal()) {
                $info_mn_cart['subtotal'] = "";
            }
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping quote right now.'));
            $this->logger->critical($e);
            return $resultJson->setData($result);
        }
    }

    /**
     * @param Product $product
     * @param array $info_mn_cart
     * @param null|\Magento\Quote\Model\Quote\Item $cartItem
     * @return mixed
     */
    protected function getContentPopup($product, $info_mn_cart, $cartItem = null)
    {
        $template = 'Bss_AddMultipleProducts::popup_quote_extension.phtml';
        $html = $this->layout
            ->createBlock(\Bss\AddMultipleProducts\Block\OptionProduct::class)
            ->setTemplate($template)
            ->setProduct($product)
            ->setCart($info_mn_cart)
            ->setTypeadd('single');
        if ($cartItem) {
            if($product->getCanShowPrice() === false) {
                $html->setPrice(0);
            } else {
                $html->setPrice($cartItem->getPrice());
            }

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
        return $this->_url->getUrl('quoteextension/quote', ['_secure' => true]);
    }
}
