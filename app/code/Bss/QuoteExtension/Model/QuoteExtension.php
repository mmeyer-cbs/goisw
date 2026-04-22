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
namespace Bss\QuoteExtension\Model;

/**
 * Class QuoteExtension
 *
 * @package Bss\QuoteExtension\Model
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteExtension extends \Magento\Checkout\Model\Cart
{
    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quoteextension')) {
            $this->setData('quoteextension', $this->_checkoutSession->getQuoteExtension());
        }
        return $this->_getData('quoteextension');
    }

    /**
     * Save Request Quote
     *
     * @return $this|\Magento\Checkout\Model\Cart
     * @throws \Exception
     */
    public function save()
    {
        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();

        $quote = $this->getQuote();
        $quote->save();

        $this->_checkoutSession->setQuoteExtensionId($this->getQuote()->getId());

        /**
         * Cart save usually called after changes with cart items.
         */
        //$this->_eventManager->dispatch('checkout_cart_save_after', ['cart' => $this]);
        $this->reinitializeState();
        return $this;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = $this->_checkoutSession->getQuoteExtensionId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && $this->_customerSession->isLoggedIn()) {
            $this->_checkoutSession->getQuoteExtension();
            $quoteId = $this->_checkoutSession->getQuoteExtensionId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            $useQty = $this->_scopeConfig->getValue(
                'checkout/cart_link/use_qty',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->_summaryQty = $useQty ? $this->getItemsQty() : $this->getItemsCount();
        }
        return $this->_summaryQty;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();

        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty
                && $minimumQty > 0
                && !$request->getQty()
                && !$this->getQuote()->hasProductId($productId)
            ) {
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            $this->checkResult($result, $product);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }
        return $this;
    }

    /**
     * Update cart items information
     *
     * @param  array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateItems($data)
    {
        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $this->formatCustomPrice($itemInfo);
            $this->formatDescription($itemInfo);
            $item = $this->getQuote()->getItemById($itemId);
            if (!$this->checkItem($item, $itemInfo, $itemId)) {
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;
            if ($qty > 0) {
                $item->setQty($qty);

                $update_price = false;

                $this->checkCustomPrice($itemInfo, $item, $qty, $update_price);

                $this->setDescriptionForItem($itemInfo, $item);

                if ($update_price && $item->getHasConfigurationUnavailableError()) {
                    $item->unsHasConfigurationUnavailableError();
                }

                $this->returnErrorMess($item);
                $this->returnNoticeMess($item, $itemInfo, $qtyRecalculatedFlag, $qty);
            }
        }

        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNoticeMessage(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        return $this;
    }

    /**
     * Format Price
     *
     * @param array $itemInfo
     */
    protected function formatCustomPrice(&$itemInfo)
    {
        if (isset($itemInfo['customprice'])) {
            $itemInfo['customprice'] = (float) $itemInfo['customprice'];
        }
    }

    /**
     * Format Description
     *
     * @param array $itemInfo
     */
    protected function formatDescription(&$itemInfo)
    {
        if (isset($itemInfo['description']) && $itemInfo['description']) {
            $itemInfo['description'] = strip_tags(trim($itemInfo['description']));
        }
    }

    /**
     * Break to next foreach if return false
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $itemInfo
     * @param int $itemId
     * @return bool
     */
    protected function checkItem($item, $itemInfo, $itemId)
    {
        if (!$item) {
            return false;
        }

        if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
            $this->removeItem($itemId);
            return false;
        }
        return true;
    }

    /**
     * Check item have custom price
     *
     * @param array $itemInfo
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param float $qty
     * @param bool $update_price
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkCustomPrice($itemInfo, $item, $qty, &$update_price)
    {
        if (isset($itemInfo['customprice']) &&
            $itemInfo['customprice'] && $itemInfo['customprice'] != ($item->getPrice() * $qty)) {
            if (!$item->getCustomPrice()) {
                $price  = [
                    'price' => $item->getPrice(),
                    'base_price' => $item->getBasePrice(),
                    'price_incl_tax' => $item->getPriceInclTax(),
                    'base_price_incl_tax' => $item->getBasePriceInclTax(),
                ];

                $option = [
                    'product_id' => $item->getProductId(),
                    'code' => 'product_price',
                    'value' => $price // plugin convert array to serialize
                ];
                $item->addOption($option);
            }

            $customPrice = $itemInfo['customprice'] / $qty;
            $item->setCustomPrice($customPrice);
            $item->setOriginalCustomPrice($customPrice);
            $update_price = true;
        }
    }

    /**
     * Set description for item
     *
     * @param array $itemInfo
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    protected function setDescriptionForItem($itemInfo, $item)
    {
        if (isset($itemInfo['description']) && $itemInfo['description']) {
            $item->setDescription($itemInfo['description']);
        }
    }

    /**
     * Return Error message
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function returnErrorMess($item)
    {
        if ($item->getHasError()) {
            throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
        }
    }

    /**
     * Return Notice message
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $itemInfo
     * @param bool $qtyRecalculatedFlag
     * @param float $qty
     */
    protected function returnNoticeMess($item, $itemInfo, &$qtyRecalculatedFlag, $qty)
    {
        if (isset($itemInfo['before_suggest_qty']) && $itemInfo['before_suggest_qty'] != $qty) {
            $qtyRecalculatedFlag = true;
            $this->messageManager->addNotice(
                __('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty),
                'quote_item' . $item->getId()
            );
        }
    }

    /**
     * Set reidrect url if have error
     *
     * @param string $result
     * @param \Magento\Catalog\Model\Product $product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkResult($result, $product)
    {
        if (is_string($result)) {
            if ($product->hasOptionsValidationFail()) {
                $redirectUrl = $product->getUrlModel()->getUrl(
                    $product,
                    ['_query' => ['startcustomization' => 1]]
                );
            } else {
                $redirectUrl = $product->getProductUrl();
            }
            $this->_checkoutSession->setRedirectUrl($redirectUrl);
            if ($this->_checkoutSession->getUseNotice() === null) {
                $this->_checkoutSession->setUseNotice(true);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }
    }
}
