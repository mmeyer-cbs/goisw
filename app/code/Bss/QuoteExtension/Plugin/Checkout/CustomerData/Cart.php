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
namespace Bss\QuoteExtension\Plugin\Checkout\CustomerData;

/**
 * Class Cart
 * @package Bss\QuoteExtension\Plugin\Checkout\CustomerData
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Cart
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $quoteSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var array|null
     */
    protected $totals = null;

    /**
     * @param \Magento\Checkout\Model\Session $quoteSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
     */
    public function __construct(
        \Magento\Checkout\Model\Session $quoteSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
    ) {
        $this->quoteSession = $quoteSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
    }

    /**
     * Add tax data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Bss\QuoteExtension\CustomerData\QuoteExtension $subject, $result)
    {
        $result['subtotal_incl_tax'] = $this->checkoutHelper->formatPrice($this->getSubtotalInclTax());
        $result['subtotal_excl_tax'] = $this->checkoutHelper->formatPrice($this->getSubtotalExclTax());

        $items =$this->getQuote()->getAllVisibleItems();
        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    if ($item->getProduct()->getCanShowPrice() !== false) {
                        $this->itemPriceRenderer->setItem($item);
                        $this->itemPriceRenderer->setTemplate('Magento_Tax::checkout/cart/item/price/sidebar.phtml');
                        $result['items'][$key]['product_price'] = $this->itemPriceRenderer->toHtml();
                        if ($this->itemPriceRenderer->displayPriceExclTax()) {
                            $result['items'][$key]['product_price_value'] = $item->getCalculationPrice();
                        } elseif ($this->itemPriceRenderer->displayPriceInclTax()) {
                            $result['items'][$key]['product_price_value'] = $item->getPriceInclTax();
                        } elseif ($this->itemPriceRenderer->displayBothPrices()) {
                            //unset product price value in case price already has been set as scalar value.
                            unset($result['items'][$key]['product_price_value']);
                            $result['items'][$key]['product_price_value']['incl_tax'] = $item->getPriceInclTax();
                            $result['items'][$key]['product_price_value']['excl_tax'] = $item->getCalculationPrice();
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get subtotal, including tax
     *
     * @return float
     */
    protected function getSubtotalInclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueInclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get subtotal, excluding tax
     *
     * @return float
     */
    protected function getSubtotalExclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueExclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get totals
     *
     * @return array
     */
    public function getTotals()
    {
        // TODO: TODO: MAGETWO-34824 duplicate \Magento\Checkout\CustomerData\Cart::getSectionData
        if (empty($this->totals)) {
            $this->totals = $this->getQuote()->getTotals();
        }
        return $this->totals;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->quoteSession->getQuoteExtension();
        }
        return $this->quote;
    }

    /**
     * Find item by id in items haystack
     *
     * @param int $id
     * @param array $itemsHaystack
     * @return \Magento\Quote\Model\Quote\Item | bool
     */
    protected function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }
}
