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
namespace Bss\QuoteExtension\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class QuoteExtension
 *
 * @package Bss\QuoteExtension\CustomerData
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteExtension extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Bss\QuoteExtension\Model\Session
     */
    protected $quoteSession;

    /**
     * @var \Bss\QuoteExtension\Model\QuoteExtension
     */
    protected $quoteExtension;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $catalogUrl;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var ItemPool
     */
    protected $itemPoolInterface;

    /**
     * @var int|float
     */
    protected $summeryCount;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObject;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * QuoteExtension constructor.
     * @param \Magento\Checkout\Model\Session $quoteSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Bss\QuoteExtension\Model\QuoteExtension $quoteExtension
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Bss\QuoteExtension\CustomerData\ItemPoolInterface $itemPoolInterface
     * @param \Magento\Framework\DataObjectFactory $dataObject
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $quoteSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Bss\QuoteExtension\Model\QuoteExtension $quoteExtension,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Bss\QuoteExtension\CustomerData\ItemPoolInterface $itemPoolInterface,
        \Magento\Framework\DataObjectFactory $dataObject,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
    ) {
        parent::__construct($data);
        $this->quoteSession = $quoteSession;
        $this->catalogUrl = $catalogUrl;
        $this->quoteExtension = $quoteExtension;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPoolInterface = $itemPoolInterface;
        $this->dataObject = $dataObject;
        $this->layout = $layout;
    }

    /**
     * { @inheritdoc }
     */
    public function getSectionData()
    {
        $totals = $this->getQuote()->getTotals();
        $subtotalAmount = $totals['subtotal']->getValue();
        return [
            'summary_count' => $this->getSummaryCount(),
            'subtotalAmount' => $subtotalAmount,
            'subtotal' => isset($totals['subtotal']) && $this->canShowSubtotal()
                ? $this->checkoutHelper->formatPrice($subtotalAmount)
                : '',
            'possible_onepage_checkout' => $this->isPossibleOnepageCheckout(),
            'items' => $this->getRecentItems(),
            'extra_actions' => $this->layout
                ->createBlock(\Magento\Catalog\Block\ShortcutButtons::class)->toHtml(),
            'website_id' => $this->getQuote()->getStore()->getWebsiteId()
        ];
    }

    /**
     * Get active quote
     *
     * @return \Bss\QuoteExtension\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->quoteSession->getQuoteExtension();
        }
        return $this->quote;
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    protected function getSummaryCount()
    {
        if (!$this->summeryCount) {
            $this->summeryCount = $this->quoteExtension->getSummaryQty() ?: 0;
        }
        return $this->summeryCount;
    }

    /**
     * Check if one page checkout is available
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isPossibleOnepageCheckout()
    {
        return $this->checkoutHelper->canOnepageCheckout() && !$this->getQuote()->getHasError();
    }

    /**
     * Get array of last added items
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (!isset($products[$product->getId()])) {
                    continue;
                }
                $urlDataObject = $this->dataObject->create();
                $urlDataObject->setData($products[$product->getId()]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
            $items[] = $this->itemPoolInterface->getItemData($item);
        }
        return $items;
    }

    /**
     * Return customer quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAllQuoteItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllVisibleItems();
        }
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function canShowSubtotal()
    {
        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if ($item->getProduct()->getCanShowPrice() === false) {
                return false;
            }
        }
        return true;
    }
}
