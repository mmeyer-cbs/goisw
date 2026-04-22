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
namespace Bss\QuoteExtension\Controller\WishList;

use Magento\Framework\Controller\Result\Redirect;
use Bss\QuoteExtension\Controller\Quote\Add as QuoteAdd;
use Bss\QuoteExtension\Helper\HelperClass;
use Bss\QuoteExtension\Helper\QuoteExtension\AddToQuote;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Bss\QuoteExtension\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\ResourceModel\Item\Option\Collection;
use Magento\Catalog\Helper\Product as ProductHelper;

/**
 * Class Add extend cQuote add that proceed add item from wishlist to quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Add extends QuoteAdd
{
    /**
     * @var LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * Add constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $quoteExtensionSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param PageFactory $resultPageFactory
     * @param ManageQuote $manageQuote
     * @param AddToQuote $helperAddToQuote
     * @param HelperClass $helperClass
     * @param OptionFactory $optionFactory
     * @param ItemFactory $itemFactory
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductHelper $productHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocaleQuantityProcessor $quantityProcessor,
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $quoteExtensionSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        PageFactory $resultPageFactory,
        ManageQuote $manageQuote,
        AddToQuote $helperAddToQuote,
        HelperClass $helperClass,
        OptionFactory $optionFactory,
        ItemFactory $itemFactory,
        WishlistProviderInterface $wishlistProvider,
        ProductHelper $productHelper
    ) {
        $this->quantityProcessor = $quantityProcessor;
        $this->optionFactory = $optionFactory;
        $this->itemFactory = $itemFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->productHelper = $productHelper;
        parent::__construct(
            $context,
            $scopeConfig,
            $quoteExtensionSession,
            $storeManager,
            $formKeyValidator,
            $quoteExtension,
            $resultPageFactory,
            $manageQuote,
            $helperAddToQuote,
            $helperClass
        );
    }

    /**
     * Add product to shopping cart action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return Redirect|void
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }
        $itemId = (int)$this->getRequest()->getParam('item_id');

        $item = $this->itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }

        $this->setQty($item);
        $params = $this->getRequest()->getParams();
        try {
            /** @var Collection $options */
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));
            $this->fillterQty($params);
            $product = $this->_initProduct();
            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $buyRequest = $this->productHelper->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                ['current_config' => $item->getBuyRequest()]
            );
            $item->mergeBuyRequest($buyRequest);
            $related = $this->getRequest()->getParam('related_product');
            $this->addProducts($product, $item->getBuyRequest(), $related);
            if (!$this->quoteExtensionSession->getNoCartRedirect(true)) {
                $hasError = $this->quoteExtension->getQuote()->getHasError();
                $this->getSuccessMess($hasError, $product);
                return $this->goBack(null, $product);
            }
        } catch (LocalizedException $e) {
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

    /**
     * Set qty for item
     *
     * @param \Magento\Wishlist\Model\Item $item
     */
    public function setQty($item)
    {
        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        $postQty = $this->getRequest()->getPostValue('qty');
        if ($postQty !== null && $qty !== $postQty) {
            $qty = $postQty;
        }
        if (is_array($qty)) {
            if (isset($qty[$item->getId()])) {
                $qty = $qty[$item->getId()];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->quantityProcessor->process($qty);
        if ($qty) {
            $item->setQty($qty);
        }
    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     * @param \Magento\Catalog\Model\Product|null $product
     * @return Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl) {
            $resultRedirect->setUrl($backUrl);
            return $resultRedirect;
        }
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl && strpos($refererUrl, 'customer/section/load') !== false) {
            $backUrl = $this->_url->getUrl('wishlist/index/index');
        }

        if ($backUrl || $backUrl = $this->getBackUrl($refererUrl)) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }
}
