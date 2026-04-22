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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Catalog\Model\Product;

/**
 * Class Items
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit
 */
class Items extends \Magento\Backend\Block\Widget
{
    /**
     * Contains button descriptions to be shown at the top of accordion
     *
     * @var array
     */
    protected $buttons = [];

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteItems
     */
    protected $helperQuoteItems;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $orderCreate;

    /**
     * Items constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Bss\QuoteExtension\Helper\QuoteItems $helperQuoteItems
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Bss\QuoteExtension\Helper\QuoteItems $helperQuoteItems,
        array $data = []
    ) {
        $this->orderCreate = $orderCreate;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManagerInterface;
        $this->helperQuoteItems = $helperQuoteItems;
        parent::__construct($context, $data);
    }

    /**
     * Define block ID
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quote_extension_quote_items');
    }

    /**
     * Convert Price for item
     *
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemPrice(Product $product)
    {
        $price = $product->getPriceInfo()->getPrice($this->helperQuoteItems->returnFinalPriceCode())->getValue();
        return $this->helperQuoteItems->getHelperData()->formatPrice($price);
    }

    /**
     * If item is quote or wishlist we need to get product from it.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return Product
     */
    public function getProduct($item)
    {
        if ($item instanceof Product) {
            $product = $item;
        } else {
            $product = $item->getProduct();
        }

        return $product;
    }

    /**
     * Accordion header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Items Quote');
    }

    /**
     * Returns all visible items
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get quote cart
     *
     * @return object
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry('mage_quote');
    }

    /**
     * Get request quote extension
     *
     * @return object
     */
    public function getManageQuote()
    {
        return $this->coreRegistry->registry('quoteextension_quote');
    }

    /**
     * Add button to the items header
     *
     * @param array $args
     * @return void
     */
    public function addButton($args)
    {
        $this->buttons[] = $args;
    }

    /**
     * Render buttons and return HTML code
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonsHtml()
    {
        if (!$this->canShowButtonAction()) {
            return '';
        }
        $html = '<div class="actions">';
        // Make buttons to be rendered in opposite order of addition. This makes "Add products" the last one.
        $this->buttons = array_reverse($this->buttons);
        foreach ($this->buttons as $buttonData) {
            $html .= $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                $buttonData
            )->toHtml();
        }
        $html.='</div>';

        return $html;
    }

    /**
     * Get Button Html
     *
     * @param string $label
     * @param string $onclick
     * @param string $class
     * @param int $buttonId
     * @param array $dataAttr
     * @return string
     */
    public function getButtonHtml($label, $onclick, $class = '', $buttonId = null, $dataAttr = [])
    {
        if (!$this->canShowButtonAction()) {
            return '';
        }
        return parent::getButtonHtml($label, $onclick, $class, $buttonId, $dataAttr);
    }

    /**
     * Return back can show button action.
     *
     * @return bool
     */
    protected function canShowButtonAction()
    {
        $mageQuote = $this->getManageQuote();
        if ($mageQuote && $mageQuote->getId()) {
            $quoteStatus = $mageQuote->getStatus();
            $ignore = [Status::STATE_CANCELED, Status::STATE_ORDERED , Status::STATE_REJECTED, Status::STATE_UPDATED];
            if (in_array($quoteStatus, $ignore)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return HTML code of the block
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if ($this->getStoreId()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get store manager
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Get Store Id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getStoreId();
    }
}
