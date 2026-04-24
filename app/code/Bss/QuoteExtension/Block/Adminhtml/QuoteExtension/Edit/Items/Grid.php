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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items;

use Bss\QuoteExtension\Model\QuoteExtension;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Helper\Data;

/**
 * Adminhtml sales order create items grid block
 *
 * Class Grid
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grid extends \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items
{
    /**
     * Flag to check can items be move to customer storage
     *
     * @var bool
     */
    protected $moveToCustomerStorage = true;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Bss\QuoteExtension\Helper\HidePriceEmail
     */
    protected $hidePriceEmail;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $helperData;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Bss\QuoteExtension\Helper\QuoteItems $helperQuoteItems
     * @param StockStateInterface $stockState
     * @param \Bss\QuoteExtension\Helper\HidePriceEmail $hidePriceEmail
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Bss\QuoteExtension\Helper\QuoteItems $helperQuoteItems,
        StockStateInterface $stockState,
        \Bss\QuoteExtension\Helper\HidePriceEmail $hidePriceEmail,
        Data $helperData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $orderCreate,
            $coreRegistry,
            $storeManagerInterface,
            $helperQuoteItems,
            $data
        );
        $this->stockState = $stockState;
        $this->hidePriceEmail = $hidePriceEmail;
        $this->helperData = $helperData;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_search_grid');
    }

    /**
     * Get items
     *
     * @return \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Item[]
     */
    public function getItems()
    {
        $items = $this->getParentBlock()->getItems();
        $oldSuperMode = $this->getQuote()->getIsSuperMode();
        $this->getQuote()->setIsSuperMode(false);
        foreach ($items as $item) {
            // To dispatch inventory event sales_quote_item_qty_set_after, set item qty
            $item->setQty($item->getQty());

            if (!$item->getMessage()) {
                //Getting product ids for stock item last quantity validation before grid display
                $stockItemToCheck = [];

                $childItems = $item->getChildren();
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        $stockItemToCheck[] = $childItem->getProduct()->getId();
                    }
                } else {
                    $stockItemToCheck[] = $item->getProduct()->getId();
                }

                foreach ($stockItemToCheck as $productId) {
                    $check = $this->stockState->checkQuoteItemQty(
                        $productId,
                        $item->getQty(),
                        $item->getQty(),
                        $item->getQty(),
                        $this->getQuote()->getStore()->getWebsiteId()
                    );
                    $item->setMessage($check->getMessage());
                    $item->setHasError($check->getHasError());
                }
            }

            if ($item->getProduct()->getStatus() == $this->helperQuoteItems->returnDisableStatus()) {
                $item->setMessage(__('This product is disabled.'));
                $item->setHasError(true);
            }
        }
        $this->getQuote()->setIsSuperMode($oldSuperMode);
        return $items;
    }

    /**
     * Get session
     *
     * @return mixed
     */
    public function getSession()
    {
        return $this->getParentBlock()->getSession();
    }

    /**
     * Get item editable price
     *
     * @param Item $item
     * @return float
     */
    public function getItemEditablePrice($item)
    {
        return $item->getCalculationPrice() * 1;
    }

    /**
     * Get original editable price
     *
     * @param Item $item
     * @return float
     */
    public function getOriginalEditablePrice($item)
    {
        if ($item->hasOriginalCustomPrice()) {
            $result = $item->getOriginalCustomPrice() * 1;
        } elseif ($item->hasCustomPrice()) {
            $result = $item->getCustomPrice() * 1;
        } else {
            if ($this->helperQuoteItems->isPriceIncludesTax($this->getQuote()->getStore())) {
                $result = $item->getPriceInclTax() * 1;
            } else {
                $result = $item->getOriginalPrice() * 1;
            }
        }
        return $result;
    }

    /**
     * Get item original price
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemOrigPrice($item)
    {
        return $this->formatPrice($item->getPrice());
    }

    /**
     * Check if we need display grid totals include tax
     *
     * @return bool
     */
    public function displayTotalsIncludeTax()
    {
        $result = $this->helperQuoteItems->displayTotalsIncludeTax($this->getQuote()->getStore());
        return $result;
    }

    /**
     * Get subtotal
     *
     * @return false|float
     */
    public function getSubtotal()
    {
        $address = $this->getQuoteAddress();
        if (!$this->displayTotalsIncludeTax()) {
            return $address->getSubtotal();
        }
        if ($address->getSubtotalInclTax()) {
            return $address->getSubtotalInclTax();
        }
        return $address->getSubtotal() + $address->getTaxAmount();
    }

    /**
     * Get subtotal with discount
     *
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        $address = $this->getQuoteAddress();
        $requestQuote = $this->getQuote();
        $shippingTaxClass = $this->helperData->getShippingTaxClass($requestQuote->getStore());
        if ($this->displayTotalsIncludeTax()) {
            $taxAmount = $address->getTaxAmount();
            if ($taxAmount > 0 && $shippingTaxClass) {
                $shippingTax = 0;
                $appliedTaxes = $address->getAppliedTaxes();
                if (!empty($appliedTaxes)) {
                    foreach ($appliedTaxes as $tax) {
                        if (isset($tax['percent'])) {
                            $shippingTax += $address->getShippingAmount() * $tax['percent'] / 100;
                        }
                    }
                }
                $taxAmount -= $shippingTax;
            }
            $taxAmount += $this->calculateFeeTax($requestQuote);
            return $address->getSubtotal()
                + $taxAmount
                + $address->getDiscountAmount()
                + $address->getDiscountTaxCompensationAmount();
        } else {
            return $address->getSubtotal() + $address->getDiscountAmount();
        }
    }

    /**
     * Caculate fixed product tax
     *
     * @param QuoteExtension $requestQuote
     * @return float|int
     */
    protected function calculateFeeTax($requestQuote)
    {
        $feeTax = 0;
        foreach ($requestQuote->getAllVisibleItems() as $item) {
            $weeeTax = (float)$item->getWeeeTaxAppliedRowAmount();
            $feeTax += $weeeTax;
        }
        return $feeTax;
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->getQuote()->getShippingAddress()->getDiscountAmount();
    }

    /**
     * Retrieve quote address
     *
     * @return mixed
     */
    public function getQuoteAddress()
    {
        return $this->helperQuoteItems->getQuoteAddress($this->getQuote());
    }

    /**
     * Define if specified item has already applied custom price
     *
     * @param Item $item
     * @return bool
     */
    public function usedCustomPriceForItem($item)
    {
        return $item->hasCustomPrice();
    }

    /**
     * Define if custom price can be applied for specified item
     *
     * @param Item $item
     * @return bool
     */
    public function canApplyCustomPrice($item)
    {
        return !$item->isChildrenCalculated();
    }

    /**
     * Get qty title
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQtyTitle($item)
    {
        $prices = $item->getProduct()
            ->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE)
            ->getTierPriceList();
        if ($prices) {
            $info = [];
            foreach ($prices as $data) {
                $price = $this->formatPrice($data['price']);
                $info[] = __('Buy %1 for price %2', $data['price_qty'], $price);
            }
            return implode(', ', $info);
        } else {
            return __('Item ordered qty');
        }
    }

    /**
     * Get tier price html
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTierHtml($item)
    {
        $html = '';
        $prices = $item->getProduct()->getTierPrice();
        if ($prices) {
            if ($item->getProductType() == $this->helperQuoteItems->returnBundlesType()) {
                $info = $this->getBundleTierPriceInfo($prices);
            } else {
                $info = $this->getTierPriceInfo($prices);
            }

            $html = implode('<br />', $info);
        }
        return $html;
    }

    /**
     * Get tier price info to display in grid for Bundle product
     *
     * @param array $prices
     * @return string[]
     */
    protected function getBundleTierPriceInfo($prices)
    {
        $info = [];
        foreach ($prices as $data) {
            $qty = $data['price_qty'] * 1;
            $info[] = __('%1 with %2 discount each', $qty, $data['price'] * 1 . '%');
        }
        return $info;
    }

    /**
     * Get tier price info to display in grid
     *
     * @param array $prices
     * @return string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getTierPriceInfo($prices)
    {
        $info = [];
        foreach ($prices as $data) {
            $qty = $data['price_qty'] * 1;
            $price = $this->formatPrice($data['price']);
            $info[] = __('%1 for %2', $qty, $price);
        }
        return $info;
    }

    /**
     * Get Custom Options of item
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomOptions(Item $item)
    {
        $optionStr = '';
        $this->moveToCustomerStorage = true;
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $item->getProduct()->getOptionById($optionId);
                if ($option) {
                    $optionStr .= $option->getTitle() . ':';
                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);

                    $optionStr .= $group->getEditableOptionValue($quoteItemOption->getValue());
                    $optionStr .= "\n";
                }
            }
        }
        return $optionStr;
    }

    /**
     * Get flag for rights to move items to customer storage
     *
     * @return bool
     */
    public function isMoveToCustomerStorage()
    {
        return $this->moveToCustomerStorage;
    }

    /**
     * Display subtotal including tax
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function displaySubtotalInclTax($item)
    {
        if ($item->getTaxBeforeDiscount()) {
            $tax = $item->getTaxBeforeDiscount();
        } else {
            $tax = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
        }
        return $this->formatPrice($item->getRowTotal() + $tax);
    }

    /**
     * Display original price including tax
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function displayOriginalPriceInclTax($item)
    {
        $tax = 0;
        if ($item->getTaxPercent()) {
            $tax = $item->getPrice() * ($item->getTaxPercent() / 100);
        }
        return $this->formatPrice($item->getPrice() + $tax / $item->getQty());
    }

    /**
     * Display row total with discount including tax
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function displayRowTotalWithDiscountInclTax($item)
    {
        $tax = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
        return $this->formatPrice(
            $item->getRowTotal() - $item->getDiscountAmount() + $tax
        );
    }

    /**
     * Get including/excluding tax message
     *
     * @return string
     */
    public function getInclExclTaxMessage()
    {
        if ($this->helperQuoteItems->isPriceIncludesTax($this->getQuote()->getStore())) {
            return __('* - Enter custom price including tax');
        } else {
            return __('* - Enter custom price excluding tax');
        }
    }

    /**
     * Return html button which calls configure window
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigureButtonHtml($item)
    {
        $product = $item->getProduct();

        $options = ['label' => __('Configure')];
        if ($product->canConfigure()) {
            $options['onclick'] = sprintf('order.showQuoteItemConfiguration(%s)', $item->getId());
        } else {
            $options['class'] = ' disabled';
            $options['title'] = __('This product does not have any configurable options');
        }

        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData($options)->toHtml();
    }

    /**
     * Get order item extra info block
     *
     * @param Item $item
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemExtraInfo($item)
    {
        return $this->getLayout()->getBlock('order_item_extra_info')->setItem($item);
    }

    /**
     * Returns whether moving to wishlist is allowed for this item
     *
     * @param Item $item
     * @return bool
     */
    public function isMoveToWishlistAllowed($item)
    {
        return $item->getProduct()->isVisibleInSiteVisibility();
    }

    /**
     * Get the item unit price html
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemUnitPriceHtml(Item $item)
    {
        $block = $this->getLayout()->getBlock('item_unit_price');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get the item row total html
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemRowTotalHtml(Item $item)
    {
        $block = $this->getLayout()->getBlock('item_row_total');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Return html for row total with discount
     *
     * @param Item $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemRowTotalWithDiscountHtml(Item $item)
    {
        $block = $this->getLayout()->getBlock('item_row_total_with_discount');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Format Price
     *
     * @param float $price
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPrice($price)
    {
        return $this->helperQuoteItems->getHelperData()->formatPrice($price);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    public function getCanShowPrice($item)
    {
        $quote = $this->getQuote();
        $customerGroupId = $quote->getCustomerGroupId();
        $storeId = $quote->getStoreId();
        if ($item->getProductType() == 'configurable') {
            $parentProductId = $item->getProductId();
            $childProductSku = $item->getSku();
            $canShowPrice = $this->hidePriceEmail->canShowPrice(
                $parentProductId,
                $childProductSku,
                $customerGroupId,
                $storeId
            );
        } else {
            $canShowPrice = $this->hidePriceEmail->canShowPrice(
                $item->getProductId(),
                false,
                $customerGroupId,
                $storeId
            );
        }
        return $canShowPrice;
    }
}
