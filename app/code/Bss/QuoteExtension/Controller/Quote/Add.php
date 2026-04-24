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
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Add
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Bss\QuoteExtension\Controller\Quote
{
    /**
     * @var array
     */
    protected $childProductQty;

    /**
     * @var array
     */
    protected $childProductName;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote
     */
    protected $helperAddToQuote;

    /**
     * @var \Bss\QuoteExtension\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * @var int
     */
    protected $versionMagento;

    /**
     * @var array
     */
    protected $stockData;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote $helperAddToQuote
     * @param \Bss\QuoteExtension\Helper\HelperClass $helperClass
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote $helperAddToQuote,
        \Bss\QuoteExtension\Helper\HelperClass $helperClass
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $quoteExtensionSession,
            $storeManager,
            $formKeyValidator,
            $quoteExtension,
            $manageQuote,
            $resultPageFactory
        );
        $this->helperAddToQuote = $helperAddToQuote;
        $this->helperClass = $helperClass;
    }

    /**
     * Initialize product instance from request data
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->helperAddToQuote->getProductById($productId, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return Redirect|mixed|void
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();
        if (isset($params['configurable_grid_table']) && $params['configurable_grid_table'] == 'Yes') {
            try {
                $this->addChildProduct($params);
                if (!$this->quoteExtensionSession->getNoCartRedirect(true)) {
                    return $this->goBack(null);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->addErrorMessage($e);
                $url = $this->getRedirectUrl();
                return $this->goBack($url);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your quote right now.')
                );
                return $this->goBack();
            }
        } else {
            try {
                $this->fillterQty($params);

                $product = $this->_initProduct();

                /**
                 * Check product availability
                 */
                if (!$product) {
                    return $this->goBack();
                }

                $related = $this->getRequest()->getParam('related_product');
                $this->addProducts($product, $params, $related);
                if (!$this->quoteExtensionSession->getNoCartRedirect(true)) {
                    $hasError = $this->quoteExtension->getQuote()->getHasError();
                    $this->getSuccessMess($hasError, $product);
                    return $this->goBack(null, $product);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->quoteExtensionSession->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage(
                        $this->helperAddToQuote->formatEscapeHtml($e->getMessage())
                    );
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->messageManager->addErrorMessage(
                            $this->helperAddToQuote->formatEscapeHtml($message)
                        );
                    }
                }

                $url = $this->quoteExtensionSession->getRedirectUrl(true);

                if (!$url) {
                    $cartUrl = $this->helperAddToQuote->getCartUrl();
                    $url = $this->_redirect->getRedirectUrl($cartUrl);
                }

                return $this->goBack($url);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
                $this->helperAddToQuote->returnLoggerClass()->critical($e);
                return $this->goBack();
            }
        }
        return $this->goBack();
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param $product
     * @return mixed
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        return $this->getResponse()->representJson(
            $this->helperAddToQuote->jsonEncodeResult($result)
        );
    }

    /**
     * Add Products
     */
    protected function addProducts($product, $params, $related)
    {
        $this->quoteExtension->addProduct($product, $params);
        if (!empty($related)) {
            $this->quoteExtension->addProductsByIds(explode(',', $related));
        }

        $this->quoteExtension->save();
    }

    /**
     * Add Success Mess
     *
     * @param bool $hasError
     * @param $product
     */
    protected function getSuccessMess($hasError, $product)
    {
        if (!$hasError) {
            $message = __(
                'You added %1 to your quote.',
                $product->getName()
            );
            $this->messageManager->addSuccessMessage($message);
        }
    }

    /**
     * Fillter Qty
     *
     * @param array $params
     */
    protected function fillterQty(&$params)
    {
        if (isset($params['qty'])) {
            $filter = $this->helperAddToQuote->getLocalized();
            $params['qty'] = $filter->filter($params['qty']);
        }
    }


    /**
     * @param array $params
     * @param int $addType add type: 1 for addNew, 2 for update
     *
     * @return Add|\Magento\Framework\Controller\Result\Redirect|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addChildProduct($params, $addType = 1)
    {
        // check version magento 2
        $this->checkVersionMagento();
        $this->getDetailStockChildProduct($params['config_table_product_id']);
        $related = $this->getRequest()->getParam('related_product');
        $storeId = $this->storeManager->getStore()->getId();
        $count = 0;
        $options = '';
        $productParens = $this->loadProductFactory($storeId, $params);
        $this->getQtyandNameProduct($productParens);

        foreach ($params['config_table_qty'] as $id => $qty) {
            if (isset($qty) && $qty != '' && $qty > 0) {
                $product = $this->loadProductFactory($storeId, $params);
                if (!$product) {
                    return $this->goBack();
                }
                $data = [];
                if (isset($params['quoteextension']) && $params['quoteextension'] == 1) {
                    $data['quoteextension'] = 1;
                }
                $filter = $this->helperClass->returnLocalizedToNormalized()->setOptions(
                    ['locale' => $this->helperClass->returnResolverInterface()->getLocale()]
                );
                $data['qty'] = $filter->filter($qty);
                $data['product'] = $params['product'];
                $data['super_attribute'] = $params['bss_super_attribute'][$id];
                $productId = $params['config_table_product_id'][$id];
                if (!$this->hasStockChild($this->childProductQty[$productId], $productId, $qty)) {
                    $this->messageManager->addErrorMessage(
                        __('We don\'t have as many "%1" as you requested, ', $this->childProductName[$productId])
                    );
                    return $this->goBack();
                }
                $this->addProduct($product, $params, $data, $options);

                $options = $this->getOption($product, $options);

                $count++;
            }
        }

        $this->addRelatedProducts($related);

        if ($count == 0) {
            $this->messageManager->addError(__('No items add to your quote.'));
            return $this->goBack();

        } else {
            $this->quoteExtension->save()->getQuote()->collectTotals();
            $this->successMessage($productParens, $addType);
        }
    }

    /**
     * @param object $product
     */
    protected function getQtyandNameProduct($product)
    {
        $configChild = $product->getTypeInstance()->getUsedProducts($product, null);
        foreach ($configChild as $simpleProduct) {
            $this->childProductQty[$simpleProduct->getId()] = $this->getQtyofChildProduct($simpleProduct);
            $this->childProductName[$simpleProduct->getId()] = $simpleProduct->getName();
        }
    }

    /**
     * @param object $simpleProduct
     * @return mixed
     */
    protected function getQtyofChildProduct($simpleProduct)
    {
        if ($this->versionMagento != 0) {
            return  $simpleProduct->getQuantityBss();
        } else {
            return  $this->stockData[$simpleProduct->getId()]['qty'];
        }
    }

    /**
     * @param array $childIds
     * @return $this
     */
    protected function getDetailStockChildProduct($childIds)
    {
        $criteria = $this->helperClass->returnStockItemCriteriaFactory()->create();
        $criteria->setProductsFilter($childIds);
        $collection = $this->helperClass->returnStockItemRepository()->getList($criteria);
        $stockData= [];
        foreach ($collection->getItems() as $item) {
            $productId = $item->getProductId();
            $stockData[$productId] = [
                'manage_stock' => (bool)$item->getManageStock(),
                'backorders' => (bool)$item->getBackorders(),
                'qty' => $item->getQty()
            ];
        }
        $this->stockData = $stockData;
        return $this;
    }

    /**
     * @return $this
     */
    protected function checkVersionMagento()
    {
        $version = $this->helperClass->returnProductMetadata()->getVerSion();
        if (version_compare($version, '2.3.0') >= 0) {
            $this->versionMagento = 1;
        }
        return $this;
    }

    /**
     * @param int $productQty
     * @param int $productId
     * @param int $boughtQty
     * @return bool
     */
    protected function hasStockChild($productQty, $productId, $boughtQty)
    {
        $stockData = $this->stockData;
        $quantity = $stockData[$productId]['qty'];
        if ($this->versionMagento != 0) {
            $quantity = $productQty;
        }
        if ($stockData[$productId]['manage_stock'] && !$stockData[$productId]['backorders'] && $boughtQty > $quantity) {
            return false;
        }
        return true;
    }

    /**
     * @param object $e
     */
    protected function addErrorMessage($e)
    {
        if ($this->quoteExtensionSession->getUseNotice(true)) {
            $this->messageManager->addNotice(
                $this->helperClass->returnEscaper()->escapeHtml($e->getMessage())
            );
        } else {
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage(
                    $this->helperClass->returnEscaper()->escapeHtml($message)
                );
            }
        }
    }

    /**
     * @return string
     */
    protected function getRedirectUrl()
    {
        $url = $this->quoteExtensionSession->getRedirectUrl(true);
        if (!$url) {
            $cartUrl = $this->helperClass->returnHelperCart()->getCartUrl();
            $url = $this->_redirect->getRedirectUrl($cartUrl);
        }
        return $url;
    }

    /**
     * @param int $storeId
     * @param array $params
     * @return object $product
     */
    protected function loadProductFactory($storeId, $params)
    {
        $product = $this->helperClass->returnProductFactory()->create()->setStoreId($storeId)->load($params['product']);
        return $product;
    }

    /**
     * @param object $product
     * @param $type 1 for add, 2 for update
     */
    protected function successMessage($product, $type = 1)
    {
        if (!$this->quoteExtension->getQuote()->getHasError()) {
            $msg = "";
            if ($type == 1) {
                $msg = __('You added %1 to your quote.', $product->getName());
            }
            if ($type == 2) {
                $msg = __('You have updated %1 in your quote.', $product->getName());
            }
            $this->messageManager->addSuccessMessage($msg);
        }
    }

    /**
     * @param object $product
     * @param array $params
     * @param array $data
     * @param array $options
     */
    protected function addProduct($product, $params, $data, $options)
    {
        if (!empty($params['options'])) {
            $data['options'] = $params['options'];
        }
        if (!empty($options)) {
            $data['options'] = $options;
        }
        $this->quoteExtension->addProduct($product, $data);
    }

    /**
     * @param object $product
     * @param array $options
     * @return mixed
     */
    protected function getOption($product, $options)
    {
        if (empty($options)) {
            $cartItem = $this->quoteExtension->getQuote()->getItemByProduct($product);
            $options = $cartItem->getBuyRequest()->getOptions();
        }
        return $options;
    }

    /**
     * @param string $related
     */
    protected function addRelatedProducts($related)
    {
        if (!empty($related)) {
            $this->quoteExtension->addProductsByIds(explode(',', $related));
        }
    }
}
