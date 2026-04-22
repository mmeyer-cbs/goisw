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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
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
class AddMultiple extends Action
{
    /**
     * @var HelperModel
     */
    protected $helperModel;

    /**use Magento\Framework\Exception\NoSuchEntityException;

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
        parent::__construct($context);
    }

    /**
     * Get Product id
     *
     * @param int $productId
     * @return false|ProductInterface
     */
    protected function getProductId($productId)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            return $this->helperAddToQuote->getProductById($productId, $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Add Products to quote
     *
     * @param ProductInterface $product
     * @param array $params
     * @param array $related
     * @throws LocalizedException
     */
    protected function addProducts($product, $params, $related)
    {
        $this->helperModel->quoteExtension()->addProduct($product, $params);
        if (!empty($related)) {
            $this->helperModel->quoteExtension()->addProductsByIds(explode(',', $related));
        }
    }

    /**
     * Check post
     *
     * @param string $productId
     * @param string $input
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
     * Get product ids
     *
     * @return array
     */
    protected function getProductIds()
    {
        $productIds = [];
        if ($this->getRequest()->getPost('product-select')) {
            $productIds = $this->getRequest()->getPost('product-select');
        }
        $params = $this->getRequest()->getParams();
        if(isset($params["popup"])) {
            return $productIds;
        }
        $data = [];
        if(is_array($productIds)) {
            foreach ($productIds as $productId) {
                if(isset($params[$productId . "_quote_extension"])) {
                    $data[] = $productId;
                }
            }
        }
        return $data;
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException|Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $addedProducts = [];
        $product_popup = [];
        $params = $this->getRequest()->getParams();
        $productIds = $this->getProductIds();
        foreach ($productIds as $productId) {
            try {
                $product = $this->getProductId($productId);
                $qty = $this->getQty($product, $productId);

                if ($qty <= 0 || !$product) {
                    continue;
                }
                // nothing to add
                $related = $this->getRequest()->getParam('related_product');
                $params["quoteextension"] = 1;
                $params['product'] = $productId;
                $params['qty'] = $qty;
                $params['bundle_option'] = $this->checkPost($productId, 'bundle_option');
                $params['bundle_option_qty'] = $this->checkPost($productId, 'bundle_option_qty');
                $params['super_attribute'] = $this->checkPost($productId, 'super_attribute');
                $params['super_group'] = $this->checkPost($productId, 'super_group');
                $params['options'] = $this->checkPost($productId, 'options');
                $params['links'] = $this->checkPost($productId, 'links');
                $this->addProducts($product, $params, $related);

                if (!empty($related)) {
                    $this->helperModel->quoteExtension()->addProductsByIds(explode(',', $related));
                }
                if (!$this->quoteExtensionSession->getNoCartRedirect(true)) {
                    if (!$this->helperModel->quoteExtension()->getQuote()->getHasError()) {
                        $addedProducts = $this->getAddedProduct($product, $addedProducts, $params);
                    }
                }
                $result["addCartSuccess"] = 1;
            } catch (LocalizedException $e) {
                if ($this->quoteExtensionSession->getUseNotice(true)) {
                    $product_popup['errors'][$product->getId()] = ['qty' => $qty, 'mess' => $e->getMessage()];
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    $product_popup['errors'][$product->getId()] = ['qty' => $qty, 'mess' => end($messages)];
                }
                $cartItem = $this->helperModel->quoteExtension()->getQuote()->getItemByProduct($product);
                if ($cartItem) {
                    $this->helperModel->quoteExtension()->getQuote()->deleteItem($cartItem);
                }
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t add this item to your shopping quote right now.'));
                $this->logger->critical($e);
            }
        }
        $this->helperModel->quoteExtension()->save();
        $html = $this->returnCart($addedProducts, $product_popup);
        $result['popup'] = $html;
        return $resultJson->setData($result);
    }

    /**
     * Return cart
     *
     * @param array $addedProducts
     * @param array $product_popup
     * @return mixed
     * @throws Exception
     */
    protected function returnCart($addedProducts, $product_popup)
    {
        $errormessageCart = '';
        $info_mn_cart = [];
        $before_cart = $this->helperModel->quoteExtension();
        $info_mn_cart['qty'] = $before_cart->getItemsQty();
        $info_mn_cart['subtotal'] = $before_cart->getQuote()->getSubtotal();
        if ($addedProducts) {
            try {
                $this->helperModel->quoteExtension()->save()->getQuote()->collectTotals();
                if (!$this->helperModel->quoteExtension()->getQuote()->getHasError()) {
                    $products = [];
                    foreach ($addedProducts as $product) {
                        $_item = $this->helperModel->quoteExtension()->getQuote()->getItemByProduct($product);
                        if($product->getCanShowPrice() === false) {
                            $product_popup['success'][] = ['id' => $product->getId(), 'price' => ""];
                        } else {
                            $product_popup['success'][] = ['id' => $product->getId(), 'price' => $_item->getPrice()];
                        }
                        $products[] = '"' . $product->getName() . '"';
                    }
                    $after_cart = $this->helperModel->quoteExtension();
                    $info_mn_cart['qty'] = $after_cart->getItemsQty();
                    $info_mn_cart['subtotal'] = $after_cart->getQuote()->getSubtotal();
                    $this->messageManager->addSuccessMessage(
                        __('%1 product(s) have been added to shopping quote: %2.', count($addedProducts), join(', ', $products))
                    );
                }
            } catch (LocalizedException $e) {
                if ($this->quoteExtensionSession->getUseNotice(true)) {
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

                $product_popup['success'] = [];
                foreach ($addedProducts as $product) {
                    $product_popup['errors'][$product->getId()] = ['qty' => $this->getRequest()
                        ->getPost($product->getId() . '_qty', 0), 'mess' => ''];
                }
            }
        }
        if(!$this->helperModel->canShowSubtotal()) {
            $info_mn_cart['subtotal'] = "";
        }
        $template = 'Bss_AddMultipleProducts::popup_quote_extension.phtml';
        $html = $this->layout
            ->createBlock(\Bss\AddMultipleProducts\Block\OptionProduct::class)
            ->setTemplate($template)
            ->setProduct($product_popup)
            ->setCart($info_mn_cart)
            ->setErrorMessageCart($errormessageCart)
            ->setTypeadd('multiple')
            ->toHtml();
        return $html;
    }

    /**
     * Get added product
     *
     * @param Product $product
     * @param array $addedProducts
     * @param array $params
     * @return array
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
     * Get child product group id
     *
     * @param Product $product
     * @param array $addedProducts
     * @param DataObject $request
     * @return array
     */
    protected function getChildProductGroupId(Product $product, $addedProducts, $request)
    {

        $processMode = AbstractType::PROCESS_MODE_FULL;
        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        foreach ($cartCandidates as $candidate) {
            $addedProducts[] = $candidate;
        }

        return $addedProducts;
    }

    /**
     * Get qty from param
     *
     * @param ProductInterface $product
     * @param int $productId
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
