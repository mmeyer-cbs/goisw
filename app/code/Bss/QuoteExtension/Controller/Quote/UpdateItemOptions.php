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
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;

/**
 * Class UpdateItemOptions
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateItemOptions extends \Bss\QuoteExtension\Controller\Quote
{
    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote
     */
    protected $helperAddToQuote;

    /**
     * @var Add
     */
    private $quoteAddAction;

    /**
     * @var \Bss\QuoteExtension\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * @var DataObject
     */
    protected $dataObject;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param Add $quoteAddAction
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Bss\QuoteExtension\Helper\HelperClass $helperClass
     * @param DataObject $dataObject
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote $helperAddToQuote
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
        Add $quoteAddAction,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\QuoteExtension\Helper\HelperClass $helperClass,
        DataObject $dataObject,
        \Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote $helperAddToQuote
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
        $this->helperClass = $helperClass;
        $this->helperAddToQuote = $helperAddToQuote;
        $this->quoteAddAction = $quoteAddAction;
        $this->dataObject = $dataObject;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Update product configuration for a quote extension item
     *
     * @return Redirect
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = [];
        }

        if ($cgvCompatible = $this->configurableGridViewProcessing($params)) {
            return $cgvCompatible;
        }

        try {
            $this->fillterQty($params);
            $quoteItem = $this->quoteExtensionSession->getQuoteExtension()->getItemById($id);
            $this->validateItem($quoteItem, true);
            $item = $this->quoteExtension->updateItem($id, $this->helperAddToQuote->createObject($params));
            $this->validateItem($item);
            $this->quoteExtension->save();

            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
            if (!$this->quoteExtensionSession->getNoCartRedirect(true)) {
                if (!$this->quoteExtensionSession->getQuoteExtension()->getHasError()) {
                    $message = __(
                        '%1 was updated in your quote extension.',
                        $item->getProduct()->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->_goBack($this->_url->getUrl('quoteextension/quote'));
            }
        } catch (LocalizedException $e) {
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
            return $this->_goBack();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the item right now.'));
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }

    /**
     * Configurable grid view compatible
     *
     * @param array $params
     * @return bool|Redirect
     */
    protected function configurableGridViewProcessing($params)
    {
        if (isset($params['configurable_grid_table']) && $params['configurable_grid_table'] == 'Yes') {
            try {
                return $this->addChildProduct($params);
            } catch (LocalizedException $e) {
                $this->messageAddError($e);
                $url = $this->checkoutSession->getRedirectUrl(true);
                return $this->returnCartUrl($url);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t update the item right now.'));
                $this->helperClass->returnLoggerInterface()->critical($e);
                return $this->_goBack();
            }
        }
        return false;
    }

    /**
     * Message Add Error
     *
     * @param object $e
     */
    protected function messageAddError($e)
    {
        if ($this->checkoutSession->getUseNotice(true)) {
            $this->messageManager->addNoticeMessage($e->getMessage());
        } else {
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        }
    }

    /**
     * Return cart url
     *
     * @param string $url
     * @return Redirect
     */
    protected function returnCartUrl($url)
    {
        if ($url) {
            return $this->resultRedirectFactory->create()->setUrl($url);
        }
        $cartUrl = $this->helperClass->returnHelperCart()->getCartUrl();
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($cartUrl));
    }

    /**
     * Add Child Product
     *
     * @param array $params
     * @return Redirect|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function addChildProduct($params)
    {
        $related = $this->getRequest()->getParam('related_product');
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($params['config_table_qty'] as $id => $qty) {
            if (isset($qty)) {
                $data = [];
                if (isset($params['quoteextension']) && $params['quoteextension'] == 1) {
                    $data['quoteextension'] = 1;
                }
                $filter = $this->helperClass->returnLocalizedToNormalized()->setOptions(
                    ['locale' => $this->helperClass->returnResolverInterface()->getLocale()]
                );
                $data['super_attribute'] = $params['bss_super_attribute'][$id];
                $data['options'] = $params['options'];
                $data['qty'] = $filter->filter($qty);

                if ($params['quote_item_id'][$id] != '') {
                    $this->checkQuoteItem($params, $id);
                    $item = $this->updateItem($data, $params, $id);
                } else {
                    $this->addProducts($data, $storeId, $params);
                }
            }
        }

        $this->addRelatedProducts($related);

        $this->quoteExtension->save();

        if (isset($item)) {
            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }
            $this->_eventManager->dispatch(
                'checkout_quote_update_item_complete',
                ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
        }

        if (!$this->checkoutSession->getNoCartRedirect(true)) {
            return $this->_goBack($this->_url->getUrl('quoteextension/quote'));
        }
    }

    /**
     * Add relate Products
     *
     * @param string $related
     */
    protected function addRelatedProducts($related)
    {
        if (!empty($related)) {
            $this->quoteExtension->addProductsByIds(explode(',', $related));
        }
    }

    /**
     * Add Products
     *
     * @param array $data
     * @param int $storeId
     * @param array $params
     * @throws LocalizedException
     */
    protected function addProducts($data, $storeId, $params)
    {
        if ($data['qty'] != '' && $data['qty'] > 0) {
            $product = $this->helperClass->returnProductFactory()
                ->create()
                ->setStoreId($storeId)
                ->load($params['product']);
            if ($product) {
                $this->quoteExtension->addProduct($product, $data);
            }
        }
    }

    /**
     * Check Quote tem
     *
     * @param array $params
     * @param int $id
     * @throws LocalizedException
     */
    protected function checkQuoteItem($params, $id)
    {
        $quoteItem = $this->quoteExtension->getQuote()->getItemById($params['quote_item_id'][$id]);
        if (!$quoteItem) {
            throw new LocalizedException(__('We can\'t find the quote item.'));
        }
    }

    /**
     * Update Iem
     *
     * @param array $data
     * @param array $params
     * @param int $id
     * @return CustomerCart|Item|string
     * @throws LocalizedException
     */
    protected function updateItem($data, $params, $id)
    {
        if ($data['qty'] != '' && $data['qty'] > 0) {
            $item = $this->quoteExtension->updateItem($params['quote_item_id'][$id], $this->dataObject->addData($data));
        } else {
            $item = $this->quoteExtension->removeItem($params['quote_item_id'][$id]);
        }
        if (is_string($item)) {
            throw new LocalizedException(__($item));
        }
        if ($item->getHasError()) {
            throw new LocalizedException(__($item->getMessage()));
        }
        return $item;
    }

    /**
     * @param $params
     */
    protected function fillterQty(&$params)
    {
        if (isset($params['qty'])) {
            $filter = $this->helperAddToQuote->getLocalized();
            $params['qty'] = $filter->filter($params['qty']);
        }
    }

    /**
     * @param $item
     * @param bool $isQuoteItem
     * @throws LocalizedException
     */
    protected function validateItem($item, $isQuoteItem = false)
    {
        if ($isQuoteItem && !$item) {
            throw new LocalizedException(
                __("The quote item isn't found. Verify the item and try again.")
            );
        }
        if (is_string($item)) {
            throw new LocalizedException(__($item));
        }
        if ($item->getHasError()) {
            throw new LocalizedException(__($item->getMessage()));
        }
    }
}
